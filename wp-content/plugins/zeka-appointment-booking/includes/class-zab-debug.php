<?php
/**
 * Debug panel for pending appointment locks and TTL monitoring.
 *
 * @package Zeka_Appointment_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZAB_Debug class - Admin debug panel for pending locks.
 */
class ZAB_Debug {

	/**
	 * Initialize debug panel.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
	}

	/**
	 * Register debug submenu page under Booking Settings.
	 *
	 * @return void
	 */
	public static function register_submenu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'options-general.php',
			'Booking Debug',
			'Booking Debug',
			'manage_options',
			'zab-debug',
			array( __CLASS__, 'render_debug_page' )
		);
	}

	/**
	 * Enqueue debug panel styles.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public static function enqueue_styles( $hook_suffix ) {
		if ( 'settings_page_zab-debug' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'zab-debug',
			plugins_url( 'assets/css/zab-debug.css', ZAB_PLUGIN_FILE ),
			array(),
			filemtime( ZAB_PLUGIN_DIR . 'assets/css/zab-debug.css' )
		);
	}

	/**
	 * Render the debug panel page.
	 *
	 * @return void
	 */
	public static function render_debug_page() {
		?>
		<div class="wrap">
			<h1>Booking Debug Panel</h1>
			<p>Monitor pending appointment locks and their TTL (Time To Live) status.</p>

			<?php self::render_time_environment_info(); ?>
			<?php self::render_pending_locks_table(); ?>
			<?php self::render_ttl_settings_info(); ?>
		</div>
		<?php
	}

	/**
	 * Render timezone and current-time diagnostics.
	 *
	 * @return void
	 */
	private static function render_time_environment_info() {
		$wordpress_timezone_string = wp_timezone_string();
		$wordpress_timezone_string = '' !== $wordpress_timezone_string ? $wordpress_timezone_string : 'UTC';
		$booking_timezone_string   = ZAB_Time::business_timezone_string();
		$booking_now               = ZAB_Time::now_local();
		$utc_now              = ZAB_Time::now_utc();
		$php_timezone         = date_default_timezone_get();
		?>
		<div class="zab-debug-panel">
			<h2>Time Environment</h2>
			<table class="wp-list-table widefat fixed">
				<tbody>
					<tr>
						<td width="30%"><strong>WordPress Timezone</strong></td>
						<td><?php echo esc_html( $wordpress_timezone_string ); ?></td>
					</tr>
					<tr>
						<td><strong>Booking Timezone</strong></td>
						<td><?php echo esc_html( $booking_timezone_string ); ?></td>
					</tr>
					<tr>
						<td><strong>Booking Local Time</strong></td>
						<td><?php echo esc_html( $booking_now->format( 'Y-m-d H:i:s P' ) ); ?></td>
					</tr>
					<tr>
						<td><strong>UTC Time</strong></td>
						<td><?php echo esc_html( $utc_now->format( 'Y-m-d H:i:s P' ) ); ?></td>
					</tr>
					<tr>
						<td><strong>PHP Default Timezone</strong></td>
						<td><?php echo esc_html( $php_timezone ); ?></td>
					</tr>
					<tr>
						<td><strong>GMT Offset</strong></td>
						<td><?php echo esc_html( (string) get_option( 'gmt_offset' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render pending locks table.
	 *
	 * @return void
	 */
	private static function render_pending_locks_table() {
		global $wpdb;

		$table_name     = $wpdb->prefix . 'booking_appointments';
		$services_table = $wpdb->prefix . 'booking_services';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$pending_locks = $wpdb->get_results(
			"SELECT a.id, a.service_id, a.customer_id, a.booking_date, a.start_time,
				a.status, a.order_id, a.lock_expires_at, s.name AS service_name
			FROM {$table_name} a
			LEFT JOIN {$services_table} s ON a.service_id = s.id
			WHERE a.status = 'pending' AND a.lock_expires_at IS NOT NULL
			ORDER BY a.lock_expires_at ASC"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $pending_locks ) ) {
			echo '<div class="notice notice-info"><p>No pending appointment locks at this time.</p></div>';
			return;
		}

		$now_utc = ZAB_Time::now_utc();

		?>
		<div class="zab-debug-panel">
			<h2>Pending Appointment Locks</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="8%">ID</th>
						<th width="20%">Service</th>
						<th width="15%">Appointment</th>
						<th width="15%">Status</th>
						<th width="20%">Expires At (UTC)</th>
						<th width="15%">Time Remaining</th>
						<th width="7%">Order ID</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pending_locks as $lock ) : ?>
						<?php
						$expiry_utc = new DateTimeImmutable(
							$lock->lock_expires_at,
							new DateTimeZone( 'UTC' )
						);
						$diff = $expiry_utc->diff( $now_utc );
						$is_expired = $now_utc >= $expiry_utc;
						$time_remaining = self::format_time_remaining( $diff, $is_expired );
						$row_class = $is_expired ? 'zab-expired' : '';
						$appointment_date = ZAB_Time::format_local_date( $lock->booking_date, 'M d, Y' );
						$appointment_time = ZAB_Time::format_utc_datetime_local( $lock->start_time, 'g:i A' );
						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>">
							<td><?php echo intval( $lock->id ); ?></td>
							<td><?php echo esc_html( $lock->service_name ?: 'Unknown Service' ); ?></td>
							<td>
								<?php echo esc_html( $appointment_date ); ?><br>
								<small><?php echo esc_html( $appointment_time ); ?></small>
							</td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $lock->status ); ?>">
									<?php echo esc_html( ucfirst( $lock->status ) ); ?>
								</span>
							</td>
							<td>
								<code><?php echo esc_html( $lock->lock_expires_at ); ?></code>
							</td>
							<td>
								<strong class="<?php echo $is_expired ? 'zab-expired-text' : ''; ?>">
									<?php echo esc_html( $time_remaining ); ?>
								</strong>
							</td>
							<td>
								<?php echo $lock->order_id ? intval( $lock->order_id ) : '—'; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render TTL settings and configuration info.
	 *
	 * @return void
	 */
	private static function render_ttl_settings_info() {
		$ttl_minutes = self::get_lock_ttl_minutes();
		?>
		<div class="zab-debug-panel">
			<h2>TTL Configuration</h2>
			<table class="wp-list-table widefat fixed">
				<tbody>
					<tr>
						<td width="30%"><strong>Current TTL (Time To Live)</strong></td>
						<td><?php echo intval( $ttl_minutes ); ?> minutes</td>
					</tr>
					<tr>
						<td><strong>Source</strong></td>
						<td>
							<?php
							if ( class_exists( 'WooCommerce' ) ) {
								$woo_minutes = get_option( 'woocommerce_hold_stock_minutes', 60 );
								echo 'WooCommerce hold_stock_minutes: ' . intval( $woo_minutes );
							} else {
								echo 'Plugin default (WooCommerce not active)';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Cleanup Method</strong></td>
						<td>Non-cron (triggered during availability queries and checkout)</td>
					</tr>
					<tr>
						<td><strong>Last Cleaned</strong></td>
						<td>
							<?php
							$last_cleanup = get_option( 'zab_last_cleanup_time' );
							if ( $last_cleanup ) {
								echo esc_html( date_i18n( 'Y-m-d H:i:s', $last_cleanup ) );
							} else {
								echo 'Never';
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Get lock TTL in minutes.
	 *
	 * @return int TTL in minutes.
	 */
	private static function get_lock_ttl_minutes() {
		if ( class_exists( 'WooCommerce' ) ) {
			$minutes = get_option( 'woocommerce_hold_stock_minutes', 60 );
			return max( 1, intval( $minutes ) );
		}
		return 15; // Default 15 minutes if WooCommerce not active.
	}

	/**
	 * Format time remaining until expiry.
	 *
	 * @param DateInterval $diff Time difference object.
	 * @param bool         $is_expired Whether the lock is expired.
	 * @return string Formatted time remaining.
	 */
	private static function format_time_remaining( $diff, $is_expired ) {
		if ( $is_expired ) {
			return 'Expired';
		}

		$hours = $diff->h + ( $diff->days * 24 );
		$minutes = $diff->i;

		if ( $hours > 0 ) {
			return sprintf( '%dh %dm', $hours, $minutes );
		}

		return sprintf( '%dm', $minutes );
	}
}
