<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_Availability {

	/**
	 * Register AJAX endpoints.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_zab_get_available_slots', array( __CLASS__, 'ajax_get_available_slots' ) );
		add_action( 'wp_ajax_nopriv_zab_get_available_slots', array( __CLASS__, 'ajax_get_available_slots' ) );
	}

	/**
	 * AJAX handler for date slot availability.
	 *
	 * @return void
	 */
	public static function ajax_get_available_slots() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'zab_booking_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid security token.', 'zeka-appointment-booking' ),
				),
				403
			);
		}

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$service_id = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;

		if ( ! self::is_valid_date( $date ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid date format.', 'zeka-appointment-booking' ),
				),
				400
			);
		}

		$slots = self::get_available_slots_for_date( $date, $service_id );

		wp_send_json_success(
			array(
				'date'       => $date,
				'service_id' => $service_id,
				'slots'      => $slots,
			)
		);
	}

	/**
	 * Build slot list for a local site date.
	 *
	 * @param string $date Site-local date in Y-m-d format.
	 * @param int    $service_id Service identifier.
	 * @return array
	 */
	public static function get_available_slots_for_date( $date, $service_id = 0 ) {
		self::release_expired_pending_locks();

		$settings     = ZAB_Settings::get_settings();
		$timezone     = ZAB_Time::business_timezone();
		$date_object  = DateTimeImmutable::createFromFormat( 'Y-m-d', $date, $timezone );
		$day_key      = $date_object ? strtolower( $date_object->format( 'l' ) ) : '';
		$working_day  = isset( $settings['working_hours'][ $day_key ] ) ? $settings['working_hours'][ $day_key ] : null;
		$duration     = self::get_service_duration( $service_id, (int) $settings['slot_duration'] );
		$buffer       = ! empty( $settings['enable_buffer'] ) ? max( 0, (int) $settings['buffer_minutes'] ) : 0;
		$min_notice   = isset( $settings['minimum_notice_minutes'] ) ? max( 0, (int) $settings['minimum_notice_minutes'] ) : 0;
		$step_minutes = $duration + $buffer;

		if ( ! is_array( $working_day ) || ! empty( $working_day['closed'] ) ) {
			return array();
		}

		if ( empty( $working_day['start'] ) || empty( $working_day['end'] ) ) {
			return array();
		}

		$exceptions = self::get_exceptions_for_date( $date );

		if ( self::has_full_day_exception( $exceptions ) ) {
			return array();
		}

		$range_start_local = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $working_day['start'], $timezone );
		$range_end_local   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $working_day['end'], $timezone );

		if ( false === $range_start_local || false === $range_end_local || $range_end_local <= $range_start_local ) {
			return array();
		}

		$blocked_ranges = self::get_blocked_appointment_ranges( $date );
		$partial_ranges = self::get_partial_exception_ranges( $exceptions, $date );
		$cutoff_local   = self::get_slot_cutoff_for_date( $date, $timezone, $min_notice );

		$slots         = array();
		$current_start = $range_start_local;

		while ( true ) {
			$current_end = $current_start->modify( '+' . $duration . ' minutes' );

			if ( $current_end > $range_end_local ) {
				break;
			}

			if ( $cutoff_local instanceof DateTimeImmutable && $current_start <= $cutoff_local ) {
				$current_start = $current_start->modify( '+' . $step_minutes . ' minutes' );
				continue;
			}

			if ( ! self::overlaps_any_range( $current_start, $current_end, $partial_ranges ) && ! self::overlaps_any_range( $current_start, $current_end, $blocked_ranges ) ) {
				$slots[] = self::build_slot_payload( $current_start, $current_end, $date );
			}

			$current_start = $current_start->modify( '+' . $step_minutes . ' minutes' );
		}

		return $slots;
	}

	/**
	 * Build the earliest allowed slot start for the requested local date.
	 *
	 * Today never shows already-started slots. Minimum notice extends that cutoff.
	 *
	 * @param string       $date Requested date in Y-m-d.
	 * @param DateTimeZone $timezone Site timezone.
	 * @param int          $min_notice Minimum notice in minutes.
	 * @return DateTimeImmutable|null
	 */
	private static function get_slot_cutoff_for_date( $date, DateTimeZone $timezone, $min_notice ) {
		$today_local = ZAB_Time::current_local_date( $timezone );

		if ( $date < $today_local ) {
			return DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date . ' 23:59:59', $timezone );
		}

		if ( $date > $today_local ) {
			return $min_notice > 0 ? ZAB_Time::now_local( $timezone )->modify( '+' . $min_notice . ' minutes' ) : null;
		}

		$cutoff_local = ZAB_Time::now_local( $timezone );

		if ( $min_notice > 0 ) {
			$cutoff_local = $cutoff_local->modify( '+' . $min_notice . ' minutes' );
		}

		return $cutoff_local;
	}

	/**
	 * Resolve slot duration for selected service.
	 *
	 * @param int $service_id Service ID.
	 * @param int $fallback_duration Default duration from settings.
	 * @return int
	 */
	private static function get_service_duration( $service_id, $fallback_duration ) {
		$fallback_duration = max( 5, $fallback_duration );

		if ( $service_id < 1 ) {
			return $fallback_duration;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_services';
		$query      = $wpdb->prepare(
			"SELECT duration_minutes FROM {$table_name} WHERE id = %d LIMIT 1",
			$service_id
		);

		$duration = $wpdb->get_var( $query );
		$duration = is_numeric( $duration ) ? (int) $duration : 0;

		if ( $duration < 5 ) {
			return $fallback_duration;
		}

		return $duration;
	}

	/**
	 * Read exceptions for a single date.
	 *
	 * @param string $date Date in Y-m-d.
	 * @return array
	 */
	private static function get_exceptions_for_date( $date ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_exceptions';
		$query      = $wpdb->prepare(
			"SELECT id, exception_date, start_time, end_time, reason FROM {$table_name} WHERE exception_date = %s",
			$date
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Determine if date has a full-day closure.
	 *
	 * @param array $exceptions Exception rows.
	 * @return bool
	 */
	private static function has_full_day_exception( $exceptions ) {
		foreach ( $exceptions as $exception ) {
			if ( empty( $exception['start_time'] ) && empty( $exception['end_time'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert partial exceptions to local datetime ranges.
	 *
	 * @param array  $exceptions Exception rows.
	 * @param string $date Date in Y-m-d.
	 * @return array
	 */
	private static function get_partial_exception_ranges( $exceptions, $date ) {
		$timezone = ZAB_Time::business_timezone();
		$ranges   = array();

		foreach ( $exceptions as $exception ) {
			if ( empty( $exception['start_time'] ) || empty( $exception['end_time'] ) ) {
				continue;
			}

			$start = self::parse_local_datetime_for_date( $date, $exception['start_time'], $timezone );
			$end   = self::parse_local_datetime_for_date( $date, $exception['end_time'], $timezone );

			if ( false === $start || false === $end || $end <= $start ) {
				continue;
			}

			$ranges[] = array(
				'start' => $start,
				'end'   => $end,
			);
		}

		return $ranges;
	}

	/**
	 * Parse local datetime from exception date and time value.
	 *
	 * Accepts both HH:MM and HH:MM:SS for backward compatibility.
	 *
	 * @param string       $date Date in Y-m-d.
	 * @param string       $time Time value.
	 * @param DateTimeZone $timezone Business timezone.
	 * @return DateTimeImmutable|false
	 */
	private static function parse_local_datetime_for_date( $date, $time, DateTimeZone $timezone ) {
		$time = is_string( $time ) ? trim( $time ) : '';

		if ( '' === $time ) {
			return false;
		}

		$datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date . ' ' . $time, $timezone );

		if ( false !== $datetime ) {
			return $datetime;
		}

		return DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $time, $timezone );
	}

	/**
	 * Fetch pending and confirmed appointment ranges for selected date.
	 *
	 * @param string $date Date in Y-m-d.
	 * @return array
	 */
	private static function get_blocked_appointment_ranges( $date ) {
		global $wpdb;

		$timezone        = ZAB_Time::business_timezone();
		$utc_timezone    = new DateTimeZone( 'UTC' );
		$local_day_start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', $timezone );
		$local_day_end   = $local_day_start ? $local_day_start->modify( '+1 day' ) : false;

		if ( false === $local_day_start || false === $local_day_end ) {
			return array();
		}

		$utc_day_start = $local_day_start->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' );
		$utc_day_end   = $local_day_end->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' );

		$table_name = $wpdb->prefix . 'booking_appointments';
		$now_utc    = current_time( 'mysql', true );
		$query      = $wpdb->prepare(
			"SELECT start_time, end_time FROM {$table_name} WHERE ( status = 'confirmed' OR ( status = 'pending' AND ( order_id > 0 OR ( lock_expires_at IS NOT NULL AND lock_expires_at > %s ) ) ) ) AND start_time < %s AND end_time > %s",
			$now_utc,
			$utc_day_end,
			$utc_day_start
		);

		$rows   = $wpdb->get_results( $query, ARRAY_A );
		$ranges = array();

		if ( ! is_array( $rows ) ) {
			return $ranges;
		}

		foreach ( $rows as $row ) {
			$start_utc = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $row['start_time'], $utc_timezone );
			$end_utc   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $row['end_time'], $utc_timezone );

			if ( false === $start_utc || false === $end_utc || $end_utc <= $start_utc ) {
				continue;
			}

			$ranges[] = array(
				'start' => $start_utc->setTimezone( $timezone ),
				'end'   => $end_utc->setTimezone( $timezone ),
			);
		}

		return $ranges;
	}

	/**
	 * Check if a slot intersects with any blocked range.
	 *
	 * @param DateTimeImmutable $slot_start Slot start.
	 * @param DateTimeImmutable $slot_end Slot end.
	 * @param array             $ranges Ranges array.
	 * @return bool
	 */
	private static function overlaps_any_range( DateTimeImmutable $slot_start, DateTimeImmutable $slot_end, $ranges ) {
		foreach ( $ranges as $range ) {
			if ( ! isset( $range['start'], $range['end'] ) || ! $range['start'] instanceof DateTimeImmutable || ! $range['end'] instanceof DateTimeImmutable ) {
				continue;
			}

			if ( $slot_start < $range['end'] && $slot_end > $range['start'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validate date string in Y-m-d format.
	 *
	 * @param string $value Date value.
	 * @return bool
	 */
	private static function is_valid_date( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}

		$date = DateTimeImmutable::createFromFormat( 'Y-m-d', $value );

		return $date && $date->format( 'Y-m-d' ) === $value;
	}

	/**
	 * Build slot payload with both business-local and UTC values.
	 *
	 * @param DateTimeImmutable $slot_start Slot start in booking timezone.
	 * @param DateTimeImmutable $slot_end Slot end in booking timezone.
	 * @param string            $date Requested booking date in booking timezone.
	 * @return array
	 */
	private static function build_slot_payload( DateTimeImmutable $slot_start, DateTimeImmutable $slot_end, $date ) {
		$utc_timezone = new DateTimeZone( 'UTC' );

		return array(
			'start'        => $slot_start->format( 'H:i' ),
			'end'          => $slot_end->format( 'H:i' ),
			'utc_start'    => $slot_start->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
			'utc_end'      => $slot_end->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
			'site_date'    => $date,
			'site_timezone'=> ZAB_Time::business_timezone_string(),
		);
	}

	/**
	 * Release orphan pending locks that expired before checkout created an order.
	 *
	 * @return void
	 */
	private static function release_expired_pending_locks() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$now_utc    = current_time( 'mysql', true );

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table_name} SET status = 'cancelled' WHERE status = 'pending' AND ( order_id IS NULL OR order_id = 0 ) AND ( lock_expires_at IS NULL OR lock_expires_at <= %s )",
				$now_utc
			)
		);
	}
}
