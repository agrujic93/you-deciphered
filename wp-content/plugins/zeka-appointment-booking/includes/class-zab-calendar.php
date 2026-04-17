<?php
/**
 * Admin appointment calendar and manual booking management.
 *
 * @package Zeka_Appointment_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZAB_Calendar class - Admin calendar and appointment management.
 */
class ZAB_Calendar {

	/**
	 * Initialize calendar admin page.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_calendar_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_zab_get_appointments', array( __CLASS__, 'ajax_get_appointments' ) );
		add_action( 'wp_ajax_zab_create_appointment', array( __CLASS__, 'ajax_create_appointment' ) );
		add_action( 'wp_ajax_zab_update_appointment', array( __CLASS__, 'ajax_update_appointment' ) );
		add_action( 'wp_ajax_zab_delete_appointment', array( __CLASS__, 'ajax_delete_appointment' ) );
	}

	/**
	 * Register calendar admin page.
	 *
	 * @return void
	 */
	public static function register_calendar_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'options-general.php',
			'Appointment Calendar',
			'Calendar',
			'manage_options',
			'zab-calendar',
			array( __CLASS__, 'render_calendar_page' )
		);
	}

	/**
	 * Enqueue calendar assets.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'settings_page_zab-calendar' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'zab-calendar',
			plugins_url( 'assets/css/zab-calendar.css', ZAB_PLUGIN_FILE ),
			array(),
			filemtime( ZAB_PLUGIN_DIR . 'assets/css/zab-calendar.css' )
		);

		wp_enqueue_script(
			'zab-calendar-js',
			plugins_url( 'assets/js/zab-calendar.js', ZAB_PLUGIN_FILE ),
			array(),
			filemtime( ZAB_PLUGIN_DIR . 'assets/js/zab-calendar.js' ),
			true
		);

		$current_time = ZAB_Time::current_local_date();

		wp_localize_script(
			'zab-calendar-js',
			'zabCalendarData',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'zab_calendar_nonce' ),
				'timeFormat'   => get_option( 'time_format', 'g:i A' ),
				'dateFormat'   => get_option( 'date_format', 'M d, Y' ),
				'currentDate'  => $current_time,
			)
		);
	}

	/**
	 * Render calendar page.
	 *
	 * @return void
	 */
	public static function render_calendar_page() {
		$current_time = ZAB_Time::current_local_date();
		list( $current_year, $current_month ) = explode( '-', $current_time );

		$month = isset( $_GET['month'] ) ? absint( wp_unslash( $_GET['month'] ) ) : (int) $current_month;
		$year  = isset( $_GET['year'] ) ? absint( wp_unslash( $_GET['year'] ) ) : (int) $current_year;

		// Validate month/year.
		$month = max( 1, min( 12, $month ) );
		$year  = max( 2000, min( 2099, $year ) );

		$first_day = new DateTimeImmutable( "$year-" . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-01' );
		$last_day  = $first_day->modify( 'last day of this month' );
		$prev_month = $first_day->modify( '-1 month' );
		$next_month = $first_day->modify( '+1 month' );

		$prev_url = add_query_arg( array( 'month' => $prev_month->format( 'm' ), 'year' => $prev_month->format( 'Y' ) ) );
		$next_url = add_query_arg( array( 'month' => $next_month->format( 'm' ), 'year' => $next_month->format( 'Y' ) ) );
		?>
		<div class="wrap">
			<h1>Appointment Calendar</h1>

			<div class="zab-calendar-header">
				<a href="<?php echo esc_url( $prev_url ); ?>" class="button">&larr; Previous</a>
				<h2><?php echo esc_html( $first_day->format( 'F Y' ) ); ?></h2>
				<a href="<?php echo esc_url( $next_url ); ?>" class="button">Next &rarr;</a>
				<button class="button button-primary" id="zab-add-appointment-btn"><?php esc_html_e( 'Add Appointment', 'zeka-appointment-booking' ); ?></button>
			</div>

			<div class="zab-calendar-wrapper">
				<?php self::render_calendar_grid( $first_day, $last_day ); ?>
			</div>

			<?php self::render_appointment_modal(); ?>
		</div>
		<?php
	}

	/**
	 * Render calendar grid.
	 *
	 * @param DateTimeImmutable $first_day First day of month.
	 * @param DateTimeImmutable $last_day Last day of month.
	 * @return void
	 */
	private static function render_calendar_grid( $first_day, $last_day ) {
		$days = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
		$first_dow = (int) $first_day->format( 'w' );
		$days_in_month = (int) $last_day->format( 'd' );

		?>
		<table class="zab-calendar-grid">
			<thead>
				<tr>
					<?php foreach ( $days as $day ) : ?>
						<th><?php echo esc_html( $day ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$day_count = 1;
				for ( $week = 0; $week < 6; $week++ ) {
					echo '<tr>';
					for ( $dow = 0; $dow < 7; $dow++ ) {
						$cell_index = ( $week * 7 ) + $dow;

						if ( $cell_index < $first_dow || $day_count > $days_in_month ) {
							echo '<td class="zab-other-month"></td>';
						} else {
							$date = $first_day->modify( '+' . ( $day_count - 1 ) . ' days' );
							$date_str = $date->format( 'Y-m-d' );
							echo '<td class="zab-calendar-cell" data-date="' . esc_attr( $date_str ) . '">';
							echo '<div class="zab-cell-day">' . esc_html( $day_count ) . '</div>';
							echo '<div class="zab-cell-appointments" id="appointments-' . esc_attr( $date_str ) . '"></div>';
							echo '</td>';
							$day_count++;
						}
					}
					echo '</tr>';
					if ( $day_count > $days_in_month ) {
						break;
					}
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render appointment modal.
	 *
	 * @return void
	 */
	private static function render_appointment_modal() {
		global $wpdb;

		$services = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}booking_services ORDER BY name ASC" );

		?>
		<div id="zab-appointment-modal" class="zab-modal">
			<div class="zab-modal-content">
				<span class="zab-modal-close">&times;</span>
				<h2 id="zab-modal-title"><?php esc_html_e( 'Add Appointment', 'zeka-appointment-booking' ); ?></h2>

				<form id="zab-appointment-form">
					<input type="hidden" id="zab-appointment-id" name="appointment_id" value="">
					<input type="hidden" id="zab-appointment-date" name="appointment_date" value="">

					<div class="form-group">
						<label for="zab-service-select"><?php esc_html_e( 'Service', 'zeka-appointment-booking' ); ?></label>
						<select id="zab-service-select" name="service_id" required>
							<option value=""><?php esc_html_e( 'Select a service...', 'zeka-appointment-booking' ); ?></option>
							<?php foreach ( $services as $service ) : ?>
								<option value="<?php echo esc_attr( $service->id ); ?>"><?php echo esc_html( $service->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="zab-customer-name"><?php esc_html_e( 'Customer Name', 'zeka-appointment-booking' ); ?></label>
						<input type="text" id="zab-customer-name" name="customer_name" placeholder="<?php esc_html_e( 'Optional', 'zeka-appointment-booking' ); ?>">
					</div>

					<div class="form-group">
						<label for="zab-customer-email"><?php esc_html_e( 'Email', 'zeka-appointment-booking' ); ?></label>
						<input type="email" id="zab-customer-email" name="customer_email" placeholder="<?php esc_html_e( 'Optional', 'zeka-appointment-booking' ); ?>">
					</div>

					<div class="form-group">
						<label for="zab-appointment-time"><?php esc_html_e( 'Start Time (HH:MM)', 'zeka-appointment-booking' ); ?></label>
						<input type="time" id="zab-appointment-time" name="appointment_time" required>
					</div>

					<div class="form-group">
						<label for="zab-appointment-status"><?php esc_html_e( 'Status', 'zeka-appointment-booking' ); ?></label>
						<select id="zab-appointment-status" name="status">
							<option value="confirmed"><?php esc_html_e( 'Confirmed', 'zeka-appointment-booking' ); ?></option>
							<option value="pending"><?php esc_html_e( 'Pending', 'zeka-appointment-booking' ); ?></option>
							<option value="awaiting_verification"><?php esc_html_e( 'Awaiting Verification', 'zeka-appointment-booking' ); ?></option>
							<option value="cancelled"><?php esc_html_e( 'Cancelled', 'zeka-appointment-booking' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Manual status changes only update the appointment. Confirming here detaches it from pending WooCommerce cleanup and does not change the Woo order status.', 'zeka-appointment-booking' ); ?></p>
					</div>

					<div class="form-actions">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Appointment', 'zeka-appointment-booking' ); ?></button>
						<button type="button" class="button zab-modal-close-btn"><?php esc_html_e( 'Cancel', 'zeka-appointment-booking' ); ?></button>
						<button type="button" id="zab-delete-btn" class="button button-danger" style="display:none;"><?php esc_html_e( 'Delete', 'zeka-appointment-booking' ); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX: Get appointments for a date.
	 *
	 * @return void
	 */
	public static function ajax_get_appointments() {
		check_ajax_referer( 'zab_calendar_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'zeka-appointment-booking' ) ), 403 );
		}

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date', 'zeka-appointment-booking' ) ), 400 );
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$services   = $wpdb->prefix . 'booking_services';
		$customers  = $wpdb->prefix . 'booking_customers';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$appointments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.id, a.service_id, a.status, a.start_time, a.end_time, a.customer_id,
					s.name as service_name, s.duration_minutes,
					c.first_name, c.last_name, c.email
				FROM {$table_name} a
				LEFT JOIN {$services} s ON a.service_id = s.id
				LEFT JOIN {$customers} c ON a.customer_id = c.id
				WHERE a.booking_date = %s
				ORDER BY a.start_time ASC",
				$date
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$timezone = ZAB_Time::business_timezone();
		$formatted = array();

		foreach ( $appointments as $appt ) {
			$start_utc = new DateTimeImmutable( $appt->start_time, new DateTimeZone( 'UTC' ) );
			$start_local = $start_utc->setTimezone( $timezone );
			$customer_name = trim( $appt->first_name . ' ' . $appt->last_name );
			if ( empty( $customer_name ) ) {
				$customer_name = $appt->email ?: __( 'No name', 'zeka-appointment-booking' );
			}

			$formatted[] = array(
				'id'            => intval( $appt->id ),
				'date'          => $date,
				'service_id'    => intval( $appt->service_id ),
				'service_name'  => $appt->service_name,
				'customer_name' => $customer_name,
				'customer_email'=> ! empty( $appt->email ) ? sanitize_email( $appt->email ) : '',
				'time'          => $start_local->format( 'g:i A' ),
				'time_24'       => $start_local->format( 'H:i' ),
				'status'        => $appt->status,
			);
		}

		wp_send_json_success( $formatted );
	}

	/**
	 * AJAX: Create appointment.
	 *
	 * @return void
	 */
	public static function ajax_create_appointment() {
		check_ajax_referer( 'zab_calendar_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'zeka-appointment-booking' ) ), 403 );
		}

		$date = isset( $_POST['appointment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['appointment_date'] ) ) : '';
		$time = isset( $_POST['appointment_time'] ) ? sanitize_text_field( wp_unslash( $_POST['appointment_time'] ) ) : '';
		$service_id = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'confirmed';
		$customer_name = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
		$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';

		if ( ! $date || ! $time || ! $service_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields', 'zeka-appointment-booking' ) ), 400 );
		}

		// Validate service exists and get duration.
		global $wpdb;

		$service = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, duration_minutes FROM {$wpdb->prefix}booking_services WHERE id = %d",
				$service_id
			)
		);

		if ( ! $service ) {
			wp_send_json_error( array( 'message' => __( 'Invalid service', 'zeka-appointment-booking' ) ), 400 );
		}

		// Convert local time to UTC.
		$timezone = ZAB_Time::business_timezone();
		$start_local = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $time, $timezone );

		if ( ! $start_local ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date/time', 'zeka-appointment-booking' ) ), 400 );
		}

		$start_utc = $start_local->setTimezone( new DateTimeZone( 'UTC' ) );
		$end_utc = $start_utc->modify( '+' . $service->duration_minutes . ' minutes' );

		// Create customer if needed.
		$customer_id = 0;
		if ( ! empty( $customer_email ) ) {
			$existing_customer = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}booking_customers WHERE email = %s",
					$customer_email
				)
			);

			if ( $existing_customer ) {
				$customer_id = absint( $existing_customer );
			} else {
				$name_parts = explode( ' ', $customer_name, 2 );
				$first_name = $name_parts[0];
				$last_name = isset( $name_parts[1] ) ? $name_parts[1] : '';

				$wpdb->insert(
					$wpdb->prefix . 'booking_customers',
					array(
						'first_name' => $first_name,
						'last_name'  => $last_name,
						'email'      => $customer_email,
					),
					array( '%s', '%s', '%s' )
				);

				$customer_id = (int) $wpdb->insert_id;
			}
		}

		// Insert appointment.
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'booking_appointments',
			array(
				'service_id'   => $service_id,
				'customer_id'  => $customer_id,
				'booking_date' => $date,
				'start_time'   => $start_utc->format( 'Y-m-d H:i:s' ),
				'end_time'     => $end_utc->format( 'Y-m-d H:i:s' ),
				'status'       => $status,
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create appointment', 'zeka-appointment-booking' ) ), 500 );
		}

		wp_send_json_success( array( 'appointment_id' => (int) $wpdb->insert_id ) );
	}

	/**
	 * AJAX: Update appointment.
	 *
	 * @return void
	 */
	public static function ajax_update_appointment() {
		check_ajax_referer( 'zab_calendar_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'zeka-appointment-booking' ) ), 403 );
		}

		$appointment_id = isset( $_POST['appointment_id'] ) ? absint( wp_unslash( $_POST['appointment_id'] ) ) : 0;
		$date = isset( $_POST['appointment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['appointment_date'] ) ) : '';
		$time = isset( $_POST['appointment_time'] ) ? sanitize_text_field( wp_unslash( $_POST['appointment_time'] ) ) : '';
		$service_id = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'confirmed';
		$customer_name = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
		$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';

		if ( ! $appointment_id || ! $date || ! $time || ! $service_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields', 'zeka-appointment-booking' ) ), 400 );
		}

		global $wpdb;

		// Verify appointment exists.
		$appt = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, service_id, order_id FROM {$wpdb->prefix}booking_appointments WHERE id = %d",
				$appointment_id
			)
		);

		if ( ! $appt ) {
			wp_send_json_error( array( 'message' => __( 'Appointment not found', 'zeka-appointment-booking' ) ), 404 );
		}

		// Get service duration.
		$service = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT duration_minutes FROM {$wpdb->prefix}booking_services WHERE id = %d",
				$service_id
			)
		);

		if ( ! $service ) {
			wp_send_json_error( array( 'message' => __( 'Invalid service', 'zeka-appointment-booking' ) ), 400 );
		}

		// Convert times.
		$timezone = ZAB_Time::business_timezone();
		$start_local = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $time, $timezone );

		if ( ! $start_local ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date/time', 'zeka-appointment-booking' ) ), 400 );
		}

		$start_utc = $start_local->setTimezone( new DateTimeZone( 'UTC' ) );
		$end_utc = $start_utc->modify( '+' . $service->duration_minutes . ' minutes' );

		// Resolve customer relation for edited appointment.
		$customer_id = 0;
		if ( ! empty( $customer_email ) ) {
			$existing_customer = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}booking_customers WHERE email = %s",
					$customer_email
				)
			);

			if ( $existing_customer ) {
				$customer_id = absint( $existing_customer );

				$name_parts = explode( ' ', $customer_name, 2 );
				$wpdb->update(
					$wpdb->prefix . 'booking_customers',
					array(
						'first_name' => ! empty( $name_parts[0] ) ? $name_parts[0] : '',
						'last_name'  => ! empty( $name_parts[1] ) ? $name_parts[1] : '',
					),
					array( 'id' => $customer_id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
			} else {
				$name_parts = explode( ' ', $customer_name, 2 );
				$first_name = ! empty( $name_parts[0] ) ? $name_parts[0] : '';
				$last_name = ! empty( $name_parts[1] ) ? $name_parts[1] : '';

				$wpdb->insert(
					$wpdb->prefix . 'booking_customers',
					array(
						'first_name' => $first_name,
						'last_name'  => $last_name,
						'email'      => $customer_email,
					),
					array( '%s', '%s', '%s' )
				);

				$customer_id = (int) $wpdb->insert_id;
			}
		}

		// Update appointment.
		$update_data = array(
			'service_id'   => $service_id,
			'customer_id'  => $customer_id,
			'booking_date' => $date,
			'start_time'   => $start_utc->format( 'Y-m-d H:i:s' ),
			'end_time'     => $end_utc->format( 'Y-m-d H:i:s' ),
			'status'       => $status,
		);

		$update_formats = array( '%d', '%d', '%s', '%s', '%s', '%s' );

		// Manual admin confirmation detaches appointment from pending Woo order flow.
		if ( 'confirmed' === $status && ! empty( $appt->order_id ) ) {
			$update_data['order_id'] = 0;
			$update_data['lock_expires_at'] = null;
			$update_formats[] = '%d';
			$update_formats[] = '%s';
		}

		$wpdb->update(
			$wpdb->prefix . 'booking_appointments',
			$update_data,
			array( 'id' => $appointment_id ),
			$update_formats,
			array( '%d' )
		);

		wp_send_json_success( array( 'message' => __( 'Appointment updated', 'zeka-appointment-booking' ) ) );
	}

	/**
	 * AJAX: Delete appointment.
	 *
	 * @return void
	 */
	public static function ajax_delete_appointment() {
		check_ajax_referer( 'zab_calendar_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'zeka-appointment-booking' ) ), 403 );
		}

		$appointment_id = isset( $_POST['appointment_id'] ) ? absint( wp_unslash( $_POST['appointment_id'] ) ) : 0;

		if ( ! $appointment_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid appointment ID', 'zeka-appointment-booking' ) ), 400 );
		}

		global $wpdb;

		$deleted = $wpdb->delete(
			$wpdb->prefix . 'booking_appointments',
			array( 'id' => $appointment_id ),
			array( '%d' )
		);

		if ( ! $deleted ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete appointment', 'zeka-appointment-booking' ) ), 500 );
		}

		wp_send_json_success( array( 'message' => __( 'Appointment deleted', 'zeka-appointment-booking' ) ) );
	}
}
