<?php
/**
 * Email notifications for appointments.
 *
 * @package Zeka_Appointment_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZAB_Emails class - Handle appointment notifications.
 */
class ZAB_Emails {

	/**
	 * Initialize email hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'zab_appointment_confirmed', array( __CLASS__, 'send_confirmation_email' ) );
		add_action( 'zab_appointment_cancelled', array( __CLASS__, 'send_cancellation_email' ) );
	}

	/**
	 * Send appointment confirmation email.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return bool
	 */
	public static function send_confirmation_email( $appointment_id ) {
		$appointment = self::get_appointment( $appointment_id );

		if ( empty( $appointment ) ) {
			return false;
		}

		$email = self::get_appointment_customer_email( $appointment );

		if ( empty( $email ) ) {
			return false;
		}

		$subject = sprintf(
			__( 'Appointment Confirmed - %s', 'zeka-appointment-booking' ),
			get_bloginfo( 'name' )
		);

		$message = self::render_confirmation_email( $appointment );

		return self::send_email( $email, $subject, $message );
	}

	/**
	 * Send appointment cancellation email.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return bool
	 */
	public static function send_cancellation_email( $appointment_id ) {
		$appointment = self::get_appointment( $appointment_id );

		if ( empty( $appointment ) ) {
			return false;
		}

		$email = self::get_appointment_customer_email( $appointment );

		if ( empty( $email ) ) {
			return false;
		}

		$subject = sprintf(
			__( 'Appointment Cancelled - %s', 'zeka-appointment-booking' ),
			get_bloginfo( 'name' )
		);

		$message = self::render_cancellation_email( $appointment );

		return self::send_email( $email, $subject, $message );
	}

	/**
	 * Get appointment data from database.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return array
	 */
	private static function get_appointment( $appointment_id ) {
		global $wpdb;

		$table_name  = $wpdb->prefix . 'booking_appointments';
		$services    = $wpdb->prefix . 'booking_services';
		$customers   = $wpdb->prefix . 'booking_customers';

		$appointment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT a.id, a.service_id, a.customer_id, a.appointment_date, a.start_time, a.end_time, a.status, a.order_id,
					s.name as service_name, s.duration_minutes,
					c.first_name, c.last_name, c.email as customer_email
				FROM {$table_name} a
				LEFT JOIN {$services} s ON a.service_id = s.id
				LEFT JOIN {$customers} c ON a.customer_id = c.id
				WHERE a.id = %d",
				$appointment_id
			),
			ARRAY_A
		);

		return is_array( $appointment ) ? $appointment : array();
	}

	/**
	 * Get customer email for appointment.
	 *
	 * @param array $appointment Appointment data.
	 * @return string
	 */
	private static function get_appointment_customer_email( $appointment ) {
		if ( ! empty( $appointment['customer_email'] ) ) {
			return sanitize_email( $appointment['customer_email'] );
		}

		if ( ! empty( $appointment['order_id'] ) && class_exists( 'WooCommerce' ) ) {
			$order = wc_get_order( $appointment['order_id'] );
			if ( $order instanceof WC_Order ) {
				return sanitize_email( $order->get_billing_email() );
			}
		}

		return '';
	}

	/**
	 * Render HTML confirmation email.
	 *
	 * @param array $appointment Appointment data.
	 * @return string
	 */
	private static function render_confirmation_email( $appointment ) {
		$site_name = get_bloginfo( 'name' );
		$home_url  = home_url( '/' );

		$appointment_date = ZAB_Time::format_local_date( $appointment['appointment_date'] );
		$start_time       = ZAB_Time::format_utc_datetime_local( $appointment['start_time'], 'g:i A' );
		$end_time         = ZAB_Time::format_utc_datetime_local( $appointment['end_time'], 'g:i A' );

		$customer_name = trim( $appointment['first_name'] . ' ' . $appointment['last_name'] );
		if ( empty( $customer_name ) ) {
			$customer_name = __( 'Valued Customer', 'zeka-appointment-booking' );
		}

		$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
		.email-body { background-color: #ffffff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		.header { background-color: #0073aa; color: #ffffff; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
		.header h1 { margin: 0; font-size: 24px; }
		.content { padding: 20px 0; }
		.appointment-details { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0; }
		.detail-row { margin: 10px 0; }
		.detail-label { font-weight: bold; color: #333; }
		.detail-value { color: #666; margin-left: 10px; }
		.button { background-color: #0073aa; color: #ffffff; padding: 10px 20px; border-radius: 3px; text-decoration: none; display: inline-block; margin: 20px 0; }
		.footer { text-align: center; color: #999; font-size: 12px; padding: 20px 0; border-top: 1px solid #e5e5e5; }
	</style>
</head>
<body>
	<div class="container">
		<div class="email-body">
			<div class="header">
				<h1>' . esc_html__( 'Appointment Confirmed!', 'zeka-appointment-booking' ) . '</h1>
			</div>
			<div class="content">
				<p>' . esc_html( sprintf( __( 'Hi %s,', 'zeka-appointment-booking' ), $customer_name ) ) . '</p>
				<p>' . esc_html__( 'Your appointment has been confirmed. Here are the details:', 'zeka-appointment-booking' ) . '</p>
				
				<div class="appointment-details">
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Service:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $appointment['service_name'] ) . '</span>
					</div>
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Date:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $appointment_date ) . '</span>
					</div>
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Time:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $start_time . ' - ' . $end_time ) . '</span>
					</div>
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Duration:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( sprintf( __( '%d minutes', 'zeka-appointment-booking' ), $appointment['duration_minutes'] ) ) . '</span>
					</div>
				</div>

				<p>' . esc_html__( 'If you need to reschedule or cancel, please contact us as soon as possible.', 'zeka-appointment-booking' ) . '</p>
				<p>' . esc_html( sprintf( __( 'Thank you for choosing %s!', 'zeka-appointment-booking' ), $site_name ) ) . '</p>
			</div>
			<div class="footer">
				<p>' . esc_html( sprintf( __( '© %s %s. All rights reserved.', 'zeka-appointment-booking' ), ZAB_Time::current_year(), $site_name ) ) . '</p>
				<p><a href="' . esc_url( $home_url ) . '">' . esc_html( $site_name ) . '</a></p>
			</div>
		</div>
	</div>
</body>
</html>';

		return $html;
	}

	/**
	 * Render HTML cancellation email.
	 *
	 * @param array $appointment Appointment data.
	 * @return string
	 */
	private static function render_cancellation_email( $appointment ) {
		$site_name = get_bloginfo( 'name' );
		$home_url  = home_url( '/' );

		$appointment_date = ZAB_Time::format_local_date( $appointment['appointment_date'] );
		$start_time       = ZAB_Time::format_utc_datetime_local( $appointment['start_time'], 'g:i A' );
		$end_time         = ZAB_Time::format_utc_datetime_local( $appointment['end_time'], 'g:i A' );

		$customer_name = trim( $appointment['first_name'] . ' ' . $appointment['last_name'] );
		if ( empty( $customer_name ) ) {
			$customer_name = __( 'Valued Customer', 'zeka-appointment-booking' );
		}

		$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
		.email-body { background-color: #ffffff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		.header { background-color: #d32f2f; color: #ffffff; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
		.header h1 { margin: 0; font-size: 24px; }
		.content { padding: 20px 0; }
		.appointment-details { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #d32f2f; margin: 20px 0; }
		.detail-row { margin: 10px 0; }
		.detail-label { font-weight: bold; color: #333; }
		.detail-value { color: #666; margin-left: 10px; }
		.button { background-color: #0073aa; color: #ffffff; padding: 10px 20px; border-radius: 3px; text-decoration: none; display: inline-block; margin: 20px 0; }
		.footer { text-align: center; color: #999; font-size: 12px; padding: 20px 0; border-top: 1px solid #e5e5e5; }
	</style>
</head>
<body>
	<div class="container">
		<div class="email-body">
			<div class="header">
				<h1>' . esc_html__( 'Appointment Cancelled', 'zeka-appointment-booking' ) . '</h1>
			</div>
			<div class="content">
				<p>' . esc_html( sprintf( __( 'Hi %s,', 'zeka-appointment-booking' ), $customer_name ) ) . '</p>
				<p>' . esc_html__( 'Your appointment has been cancelled. Here were the details:', 'zeka-appointment-booking' ) . '</p>
				
				<div class="appointment-details">
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Service:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $appointment['service_name'] ) . '</span>
					</div>
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Date:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $appointment_date ) . '</span>
					</div>
					<div class="detail-row">
						<span class="detail-label">' . esc_html__( 'Time:', 'zeka-appointment-booking' ) . '</span>
						<span class="detail-value">' . esc_html( $start_time . ' - ' . $end_time ) . '</span>
					</div>
				</div>

				<p>' . esc_html__( 'If you would like to book another appointment, please visit our website.', 'zeka-appointment-booking' ) . '</p>
				<p>' . esc_html( sprintf( __( 'Thank you for using %s!', 'zeka-appointment-booking' ), $site_name ) ) . '</p>
			</div>
			<div class="footer">
				<p>' . esc_html( sprintf( __( '© %s %s. All rights reserved.', 'zeka-appointment-booking' ), ZAB_Time::current_year(), $site_name ) ) . '</p>
				<p><a href="' . esc_url( $home_url ) . '">' . esc_html( $site_name ) . '</a></p>
			</div>
		</div>
	</div>
</body>
</html>';

		return $html;
	}

	/**
	 * Send email via wp_mail.
	 *
	 * @param string $to Email recipient.
	 * @param string $subject Email subject.
	 * @param string $message HTML message.
	 * @return bool
	 */
	private static function send_email( $to, $subject, $message ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $to, $subject, $message, $headers );
	}
}
