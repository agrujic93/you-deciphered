<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_Installer {

	/**
	 * Current database schema version.
	 */
	const DB_VERSION = '0.3.0';

	/**
	 * Create or upgrade plugin tables.
	 *
	 * @return void
	 */
	public static function activate() {
		self::install_tables();
	}

	/**
	 * Upgrade schema when plugin version changes.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$installed_version = get_option( 'zab_db_version', '0.0.0' );

		if ( version_compare( $installed_version, self::DB_VERSION, '<' ) ) {
			self::install_tables();
		}
	}

	/**
	 * Create or upgrade all plugin tables via dbDelta.
	 *
	 * @return void
	 */
	private static function install_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$services_table      = $wpdb->prefix . 'booking_services';
		$availability_table  = $wpdb->prefix . 'booking_availability';
		$appointments_table  = $wpdb->prefix . 'booking_appointments';
		$customers_table     = $wpdb->prefix . 'booking_customers';
		$exceptions_table    = $wpdb->prefix . 'booking_exceptions';

		$services_sql = "CREATE TABLE {$services_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			description text NULL,
			duration_minutes smallint(5) unsigned NOT NULL DEFAULT 60,
			price decimal(10,2) NOT NULL DEFAULT 0.00,
			PRIMARY KEY  (id),
			KEY name (name)
		) {$charset_collate};";

		$availability_sql = "CREATE TABLE {$availability_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			day_of_week tinyint(1) unsigned NOT NULL,
			start_time time NOT NULL,
			end_time time NOT NULL,
			PRIMARY KEY  (id),
			KEY day_of_week (day_of_week)
		) {$charset_collate};";

		$appointments_sql = "CREATE TABLE {$appointments_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			service_id bigint(20) unsigned NOT NULL,
			customer_id bigint(20) unsigned NULL,
			order_id bigint(20) unsigned NULL,
			booking_date date NOT NULL,
			start_time datetime NOT NULL,
			end_time datetime NOT NULL,
			lock_expires_at datetime NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			PRIMARY KEY  (id),
			KEY service_id (service_id),
			KEY customer_id (customer_id),
			KEY order_id (order_id),
			KEY booking_date (booking_date),
			KEY status (status),
			KEY slot_lookup (booking_date, start_time, status),
			KEY pending_lock (status, order_id, lock_expires_at)
		) {$charset_collate};";

		$customers_sql = "CREATE TABLE {$customers_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			first_name varchar(100) NOT NULL,
			last_name varchar(100) NOT NULL,
			email varchar(191) NOT NULL,
			phone varchar(50) NULL,
			wp_user_id bigint(20) unsigned NULL,
			PRIMARY KEY  (id),
			KEY email (email),
			KEY wp_user_id (wp_user_id)
		) {$charset_collate};";

		$exceptions_sql = "CREATE TABLE {$exceptions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			exception_date date NOT NULL,
			start_time time NULL,
			end_time time NULL,
			reason varchar(255) NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY exception_date (exception_date),
			KEY time_window (start_time, end_time)
		) {$charset_collate};";

		dbDelta( $services_sql );
		dbDelta( $availability_sql );
		dbDelta( $appointments_sql );
		dbDelta( $customers_sql );
		dbDelta( $exceptions_sql );

		update_option( 'zab_db_version', self::DB_VERSION );
	}
}
