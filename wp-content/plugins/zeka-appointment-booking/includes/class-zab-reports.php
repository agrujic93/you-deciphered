<?php
/**
 * Admin reports dashboard for appointment bookings.
 *
 * @package Zeka_Appointment_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZAB_Reports class - Admin reporting and analytics.
 */
class ZAB_Reports {

	/**
	 * Initialize reports menu.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
	}

	/**
	 * Register reports submenu page.
	 *
	 * @return void
	 */
	public static function register_submenu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'options-general.php',
			'Booking Reports',
			'Booking Reports',
			'manage_options',
			'zab-reports',
			array( __CLASS__, 'render_reports_page' )
		);
	}

	/**
	 * Enqueue reports page styles.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public static function enqueue_styles( $hook_suffix ) {
		if ( 'settings_page_zab-reports' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'zab-reports',
			plugins_url( 'assets/css/zab-reports.css', ZAB_PLUGIN_FILE ),
			array(),
			filemtime( ZAB_PLUGIN_DIR . 'assets/css/zab-reports.css' )
		);
	}

	/**
	 * Render the reports page.
	 *
	 * @return void
	 */
	public static function render_reports_page() {
		?>
		<div class="wrap">
			<h1>Booking Reports</h1>
			<p>View detailed appointment statistics and analytics.</p>

			<?php self::render_statistics_cards(); ?>
			<?php self::render_occupancy_by_service(); ?>
			<?php self::render_peak_hours(); ?>
			<?php self::render_appointment_history_table(); ?>
		</div>
		<?php
	}

	/**
	 * Render statistics overview cards.
	 *
	 * @return void
	 */
	private static function render_statistics_cards() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';

		// Total confirmed appointments.
		$total_confirmed = $wpdb->get_var(
			"SELECT COUNT(id) FROM {$table_name} WHERE status = 'confirmed'"
		);

		// Total cancelled appointments.
		$total_cancelled = $wpdb->get_var(
			"SELECT COUNT(id) FROM {$table_name} WHERE status = 'cancelled'"
		);

		// Total pending appointments.
		$total_pending = $wpdb->get_var(
			"SELECT COUNT(id) FROM {$table_name} WHERE status = 'pending'"
		);

		// Upcoming appointments (next 7 days).
		$now_utc   = ZAB_Time::now_utc();
		$next_week = $now_utc->modify( '+7 days' )->format( 'Y-m-d H:i:s' );
		$upcoming = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM {$table_name} WHERE status = 'confirmed' AND start_time <= %s AND start_time > %s",
				$next_week,
				$now_utc->format( 'Y-m-d H:i:s' )
			)
		);

		?>
		<div class="zab-stats-grid">
			<div class="stat-card">
				<div class="stat-value"><?php echo intval( $total_confirmed ); ?></div>
				<div class="stat-label"><?php esc_html_e( 'Confirmed Appointments', 'zeka-appointment-booking' ); ?></div>
			</div>
			<div class="stat-card">
				<div class="stat-value"><?php echo intval( $upcoming ); ?></div>
				<div class="stat-label"><?php esc_html_e( 'Upcoming (7 Days)', 'zeka-appointment-booking' ); ?></div>
			</div>
			<div class="stat-card">
				<div class="stat-value"><?php echo intval( $total_pending ); ?></div>
				<div class="stat-label"><?php esc_html_e( 'Pending Bookings', 'zeka-appointment-booking' ); ?></div>
			</div>
			<div class="stat-card">
				<div class="stat-value"><?php echo intval( $total_cancelled ); ?></div>
				<div class="stat-label"><?php esc_html_e( 'Cancelled', 'zeka-appointment-booking' ); ?></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render occupancy rate by service.
	 *
	 * @return void
	 */
	private static function render_occupancy_by_service() {
		global $wpdb;

		$appointments = $wpdb->prefix . 'booking_appointments';
		$services     = $wpdb->prefix . 'booking_services';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			"SELECT s.id, s.name, 
				COUNT(CASE WHEN a.status = 'confirmed' THEN 1 END) as confirmed,
				COUNT(a.id) as total
			FROM {$services} s
			LEFT JOIN {$appointments} a ON s.id = a.service_id
			GROUP BY s.id, s.name
			ORDER BY s.name ASC"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $results ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'No service or appointment data available.', 'zeka-appointment-booking' ) . '</p></div>';
			return;
		}

		?>
		<div class="zab-report-panel">
			<h2><?php esc_html_e( 'Occupancy by Service', 'zeka-appointment-booking' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="40%"><?php esc_html_e( 'Service', 'zeka-appointment-booking' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Confirmed', 'zeka-appointment-booking' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Total', 'zeka-appointment-booking' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Occupancy Rate', 'zeka-appointment-booking' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $results as $row ) : ?>
						<?php
						$total = intval( $row->total );
						$confirmed = intval( $row->confirmed );
						$rate = $total > 0 ? round( ( $confirmed / $total ) * 100 ) : 0;
						$rate_color = $rate >= 80 ? 'high' : ( $rate >= 50 ? 'medium' : 'low' );
						?>
						<tr>
							<td><?php echo esc_html( $row->name ); ?></td>
							<td><?php echo intval( $confirmed ); ?></td>
							<td><?php echo intval( $total ); ?></td>
							<td>
								<div class="occupancy-bar">
									<div class="occupancy-fill occupancy-<?php echo esc_attr( $rate_color ); ?>" style="width: <?php echo intval( $rate ); ?>%"></div>
									<span class="occupancy-text"><?php echo intval( $rate ); ?>%</span>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render peak hours analysis.
	 *
	 * @return void
	 */
	private static function render_peak_hours() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$peak_hours = $wpdb->get_results(
			"SELECT 
				HOUR(start_time) as hour,
				COUNT(id) as count
			FROM {$table_name}
			WHERE status = 'confirmed'
			GROUP BY HOUR(start_time)
			ORDER BY hour ASC"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $peak_hours ) ) {
			return;
		}

		// Convert UTC hours to booking timezone display.
		$timezone = ZAB_Time::business_timezone();
		$hour_labels = array();
		$counts = array();

		foreach ( $peak_hours as $peak ) {
			$utc_dt = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', '2024-01-01 ' . str_pad( $peak->hour, 2, '0', STR_PAD_LEFT ) . ':00', new DateTimeZone( 'UTC' ) );
			$local_dt = $utc_dt->setTimezone( $timezone );
			$hour_labels[] = $local_dt->format( 'g A' );
			$counts[] = intval( $peak->count );
		}

		?>
		<div class="zab-report-panel">
			<h2><?php esc_html_e( 'Peak Hours (Confirmed Appointments)', 'zeka-appointment-booking' ); ?></h2>
			<div class="peak-hours-chart">
				<?php foreach ( $hour_labels as $idx => $label ) : ?>
					<div class="peak-hour-bar">
						<div class="bar-container">
							<div class="bar-fill" style="height: <?php echo min( 100, max( 10, intval( $counts[ $idx ] * 10 ) ) ); ?>%"></div>
						</div>
						<div class="bar-label"><?php echo esc_html( $label ); ?></div>
						<div class="bar-count"><?php echo intval( $counts[ $idx ] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render appointment history table.
	 *
	 * @return void
	 */
	private static function render_appointment_history_table() {
		global $wpdb;

		$appointments = $wpdb->prefix . 'booking_appointments';
		$services     = $wpdb->prefix . 'booking_services';
		$customers    = $wpdb->prefix . 'booking_customers';

		// Get last 50 confirmed and cancelled appointments.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$history = $wpdb->get_results(
			"SELECT a.id, a.service_id, a.booking_date, a.start_time, a.status, a.order_id,
				s.name as service_name,
				c.first_name, c.last_name, c.email
			FROM {$appointments} a
			LEFT JOIN {$services} s ON a.service_id = s.id
			LEFT JOIN {$customers} c ON a.customer_id = c.id
			WHERE a.status IN ('confirmed', 'cancelled')
			ORDER BY a.start_time DESC
			LIMIT 50"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $history ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'No appointment history available.', 'zeka-appointment-booking' ) . '</p></div>';
			return;
		}

		?>
		<div class="zab-report-panel">
			<h2><?php esc_html_e( 'Appointment History', 'zeka-appointment-booking' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="10%"><?php esc_html_e( 'ID', 'zeka-appointment-booking' ); ?></th>
						<th width="15%"><?php esc_html_e( 'Service', 'zeka-appointment-booking' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Customer', 'zeka-appointment-booking' ); ?></th>
						<th width="20%"><?php esc_html_e( 'Date & Time', 'zeka-appointment-booking' ); ?></th>
						<th width="12%"><?php esc_html_e( 'Status', 'zeka-appointment-booking' ); ?></th>
						<th width="8%"><?php esc_html_e( 'Order', 'zeka-appointment-booking' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $history as $appt ) : ?>
						<?php
						$date_time = ZAB_Time::format_utc_datetime_local( $appt->start_time, 'M d, Y g:i A' );
						$customer_name = trim( $appt->first_name . ' ' . $appt->last_name );
						if ( empty( $customer_name ) ) {
							$customer_name = $appt->email ?: '—';
						}
						?>
						<tr>
							<td><?php echo intval( $appt->id ); ?></td>
							<td><?php echo esc_html( $appt->service_name ); ?></td>
							<td><?php echo esc_html( $customer_name ); ?></td>
							<td><?php echo esc_html( $date_time ); ?></td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $appt->status ); ?>">
									<?php echo esc_html( ucfirst( $appt->status ) ); ?>
								</span>
							</td>
							<td>
								<?php
								if ( $appt->order_id ) {
									echo intval( $appt->order_id );
								} else {
									echo '—';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
