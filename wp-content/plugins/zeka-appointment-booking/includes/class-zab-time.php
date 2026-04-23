<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_Time {

	/**
	 * Get booking/business timezone string.
	 *
	 * @return string
	 */
	public static function business_timezone_string() {
		$settings        = get_option( ZAB_Settings::OPTION_KEY, array() );
		$timezone_string = is_array( $settings ) && ! empty( $settings['timezone_string'] ) ? sanitize_text_field( $settings['timezone_string'] ) : '';

		if ( self::is_valid_timezone_string( $timezone_string ) ) {
			return $timezone_string;
		}

		$wordpress_timezone = wp_timezone_string();

		if ( self::is_valid_timezone_string( $wordpress_timezone ) ) {
			return $wordpress_timezone;
		}

		return 'UTC';
	}

	/**
	 * Get booking/business timezone object.
	 *
	 * @return DateTimeZone
	 */
	public static function business_timezone() {
		return new DateTimeZone( self::business_timezone_string() );
	}

	/**
	 * Get current booking-local date string.
	 *
	 * @param DateTimeZone|null $timezone Optional timezone override.
	 * @return string
	 */
	public static function current_local_date( ?DateTimeZone $timezone = null ) {
		$timezone = $timezone ?: self::business_timezone();

		return wp_date( 'Y-m-d', null, $timezone );
	}

	/**
	 * Get the current booking-local datetime object.
	 *
	 * @param DateTimeZone|null $timezone Optional timezone override.
	 * @return DateTimeImmutable
	 */
	public static function now_local( ?DateTimeZone $timezone = null ) {
		$timezone = $timezone ?: self::business_timezone();

		return new DateTimeImmutable( 'now', $timezone );
	}

	/**
	 * Get the current UTC datetime object.
	 *
	 * @return DateTimeImmutable
	 */
	public static function now_utc() {
		return new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Format a local booking date for output.
	 *
	 * @param string $date Booking date in Y-m-d.
	 * @param string $format Output format.
	 * @param DateTimeZone|null $timezone Optional timezone override.
	 * @return string
	 */
	public static function format_local_date( $date, $format = 'l, F j, Y', ?DateTimeZone $timezone = null ) {
		$timezone    = $timezone ?: self::business_timezone();
		$date_object = DateTimeImmutable::createFromFormat( 'Y-m-d', (string) $date, $timezone );

		if ( false === $date_object ) {
			return (string) $date;
		}

		return $date_object->format( $format );
	}

	/**
	 * Convert UTC datetime string to booking-local formatted output.
	 *
	 * @param string $utc_datetime UTC datetime string in Y-m-d H:i:s.
	 * @param string $format Output format.
	 * @param DateTimeZone|null $timezone Optional timezone override.
	 * @return string
	 */
	public static function format_utc_datetime_local( $utc_datetime, $format = 'M d, Y g:i A', ?DateTimeZone $timezone = null ) {
		$timezone    = $timezone ?: self::business_timezone();
		$date_object = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $utc_datetime, new DateTimeZone( 'UTC' ) );

		if ( false === $date_object ) {
			return (string) $utc_datetime;
		}

		return $date_object->setTimezone( $timezone )->format( $format );
	}

	/**
	 * Convert booking-local date and time to UTC datetime string.
	 *
	 * @param string              $date Local date in Y-m-d.
	 * @param string              $time Local time in H:i.
	 * @param DateTimeZone|null   $timezone Optional timezone override.
	 * @return string
	 */
	public static function local_date_time_to_utc( $date, $time, ?DateTimeZone $timezone = null ) {
		$timezone    = $timezone ?: self::business_timezone();
		$date_object = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', (string) $date . ' ' . (string) $time, $timezone );

		if ( false === $date_object ) {
			return '';
		}

		return $date_object->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Format the current site-local year.
	 *
	 * @return string
	 */
	public static function current_year() {
		return wp_date( 'Y' );
	}

	/**
	 * Validate timezone string.
	 *
	 * @param string $timezone_string Timezone candidate.
	 * @return bool
	 */
	private static function is_valid_timezone_string( $timezone_string ) {
		if ( ! is_string( $timezone_string ) || '' === $timezone_string ) {
			return false;
		}

		try {
			new DateTimeZone( $timezone_string );
			return true;
		} catch ( Exception $exception ) {
			return false;
		}
	}
}