<?php
/**
 * Plugin Name: Zeka Appointment Booking
 * Description: High-performance appointment booking plugin with custom database tables.
 * Version: 0.4.0
 * Author: Zeka
 * Text Domain: zeka-appointment-booking
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ZAB_PLUGIN_VERSION', '0.4.0' );
define( 'ZAB_PLUGIN_FILE', __FILE__ );
define( 'ZAB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once ZAB_PLUGIN_DIR . 'includes/class-zab-installer.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-time.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-settings.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-availability.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-frontend.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-woocommerce.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-emails.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-reports.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-calendar.php';
require_once ZAB_PLUGIN_DIR . 'includes/class-zab-debug.php';

/**
 * Run plugin activation routines.
 *
 * @return void
 */
function zab_activate() {
	ZAB_Installer::activate();
	ZAB_Settings::maybe_add_default_settings();
	ZAB_WooCommerce::maybe_create_booking_product();
}

register_activation_hook( ZAB_PLUGIN_FILE, 'zab_activate' );

ZAB_Installer::maybe_upgrade();
ZAB_Settings::init();
ZAB_Availability::init();
ZAB_Frontend::init();
ZAB_WooCommerce::init();
ZAB_Emails::init();
ZAB_Reports::init();
ZAB_Calendar::init();
ZAB_Debug::init();
