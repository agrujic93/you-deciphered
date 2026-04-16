<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_Frontend {

	/**
	 * Register frontend hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'zab_booking', array( __CLASS__, 'render_booking_shortcode' ) );
	}

	/**
	 * Render booking date picker and slot list.
	 *
	 * @return string
	 */
	public static function render_booking_shortcode() {
		self::enqueue_assets();

		$today               = ZAB_Time::current_local_date();
		$services            = self::get_services();
		$settings            = ZAB_Settings::get_settings();
		$single_service      = 1 === count( $services ) ? $services[0] : null;
		$single_service_text = $single_service ? self::format_service_label( $single_service ) : '';
		$booking_notice      = self::get_booking_notice();
		$cart_selection      = array();
		
		if ( class_exists( 'ZAB_WooCommerce' ) && method_exists( 'ZAB_WooCommerce', 'get_cart_booking_state' ) ) {
			$cart_selection = ZAB_WooCommerce::get_cart_booking_state();
		}
		
		$widget_id = wp_unique_id( 'zab-booking-' );

		ob_start();
		?>
		<div class="zab-booking-widget" data-zab-widget="1" id="<?php echo esc_attr( $widget_id ); ?>">
			<?php if ( ! empty( $booking_notice ) ) : ?>
				<p class="zab-booking-notice zab-booking-notice--success" role="status">
					<?php echo esc_html( $booking_notice ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $cart_selection ) && ! empty( $cart_selection['date'] ) && ! empty( $cart_selection['slots'] ) ) : ?>
				<?php $first_slot = reset( $cart_selection['slots'] ); ?>
				<p class="zab-booking-notice zab-booking-notice--info" role="status">
					<?php
					echo wp_kses_post(
						sprintf(
							__( 'You already have a booking: %s at %s - %s (%s). <a href="%s">Proceed to checkout</a> or select a different time.', 'zeka-appointment-booking' ),
							esc_html( $cart_selection['date'] ),
							esc_html( $first_slot['start'] ),
							esc_html( $first_slot['end'] ),
							esc_html( ZAB_Time::business_timezone_string() ),
							esc_url( wc_get_checkout_url() )
						)
					);
					?>
				</p>
			<?php endif; ?>

			<?php /* if ( ! empty( $single_service_text ) ) : ?>
				<p class="zab-booking-service-summary">
					<?php echo esc_html( $single_service_text ); ?>
				</p>
			<?php endif; */ ?>

			<?php if ( ! empty( $settings['enable_multiple_appointments'] ) ) : ?>
				<label class="zab-booking-multi-toggle">
					<input type="checkbox" class="zab-multi-toggle" />
					<span><?php esc_html_e( 'Book multiple slots in one checkout', 'zeka-appointment-booking' ); ?></span>
				</label>
			<?php endif; ?>

			<div class="zab-booking-widget__fields">
				<?php if ( count( $services ) > 1 ) : ?>
					<div class="zab-booking-widget__field">
						<label for="<?php echo esc_attr( $widget_id . '-service' ); ?>"><?php esc_html_e( 'Service', 'zeka-appointment-booking' ); ?></label>
						<select id="<?php echo esc_attr( $widget_id . '-service' ); ?>" class="zab-booking-service" aria-label="<?php esc_attr_e( 'Select service', 'zeka-appointment-booking' ); ?>">
							<?php foreach ( $services as $service ) : ?>
								<option value="<?php echo esc_attr( $service['id'] ); ?>"><?php echo esc_html( self::format_service_label( $service ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="zab-booking-widget__field">
					<label for="<?php echo esc_attr( $widget_id . '-date' ); ?>"><?php esc_html_e( 'Date', 'zeka-appointment-booking' ); ?></label>
					<input
						id="<?php echo esc_attr( $widget_id . '-date' ); ?>"
						type="text"
						class="zab-booking-date"
						data-min-date="<?php echo esc_attr( $today ); ?>"
						value="<?php echo esc_attr( $today ); ?>"
						aria-label="<?php esc_attr_e( 'Select date', 'zeka-appointment-booking' ); ?>"
					/>
				</div>
			</div>

			<div class="zab-slot-results" aria-live="polite"></div>
			<p class="zab-booking-timezone" aria-live="polite"></p>
			<div class="zab-booking-action" hidden>
				<p class="zab-booking-action__selected" aria-live="polite"></p>
				<button type="button" class="zab-booking-action__button">
					<?php esc_html_e( 'Continue to checkout', 'zeka-appointment-booking' ); ?>
				</button>
			</div>
			<input type="hidden" class="zab-selected-slot-start" name="zab_selected_slot_start" value="" />
			<input type="hidden" class="zab-selected-slot-end" name="zab_selected_slot_end" value="" />
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Enqueue booking UI script and data.
	 *
	 * @return void
	 */
	private static function enqueue_assets() {
		$services = self::get_services();
		$default_service_id = ! empty( $services[0]['id'] ) ? absint( $services[0]['id'] ) : 0;
		$settings           = ZAB_Settings::get_settings();
		$cart_selection     = array();

		if ( class_exists( 'ZAB_WooCommerce' ) && method_exists( 'ZAB_WooCommerce', 'get_cart_booking_state' ) ) {
			$cart_selection = ZAB_WooCommerce::get_cart_booking_state();
		}

		$style_rel_path = 'assets/css/zab-booking.css';
		$style_abs_path = ZAB_PLUGIN_DIR . $style_rel_path;
		$style_version  = file_exists( $style_abs_path ) ? (string) filemtime( $style_abs_path ) : ZAB_PLUGIN_VERSION;

		wp_enqueue_style(
			'flatpickr-style',
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array(),
			'4.6.13'
		);

		wp_enqueue_style(
			'zab-booking-style',
			plugins_url( $style_rel_path, ZAB_PLUGIN_FILE ),
			array( 'flatpickr-style' ),
			$style_version
		);

		wp_enqueue_script(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr',
			array(),
			'4.6.13',
			true
		);

		$script_rel_path = 'assets/js/zab-booking.js';
		$script_abs_path = ZAB_PLUGIN_DIR . $script_rel_path;
		$script_version  = file_exists( $script_abs_path ) ? (string) filemtime( $script_abs_path ) : ZAB_PLUGIN_VERSION;

		wp_enqueue_script(
			'zab-booking',
			plugins_url( $script_rel_path, ZAB_PLUGIN_FILE ),
			array( 'flatpickr' ),
			$script_version,
			true
		);

		wp_localize_script(
			'zab-booking',
			'zabBookingData',
			array(
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'zab_booking_nonce' ),
				'defaultServiceId'    => $default_service_id,
				'cartSelection'       => $cart_selection,
				'siteTimezone'        => ZAB_Time::business_timezone_string(),
				'freeServiceBehavior' => isset( $settings['free_service_behavior'] ) ? $settings['free_service_behavior'] : 'checkout',
				'allowMultipleAppointments' => ! empty( $settings['enable_multiple_appointments'] ),
				'redirectToCheckout'  => ! empty( $settings['redirect_to_checkout'] ),
				'labels'              => array(
					'loading'          => __( 'Loading available slots...', 'zeka-appointment-booking' ),
					'noSlots'          => __( 'No available slots for this date.', 'zeka-appointment-booking' ),
					'error'            => __( 'Could not load available slots.', 'zeka-appointment-booking' ),
					'checkoutError'    => __( 'Could not continue to checkout. Please try another slot.', 'zeka-appointment-booking' ),
					'slotsHeader'      => __( 'Available slots', 'zeka-appointment-booking' ),
					'selectedSlot'     => __( 'Selected', 'zeka-appointment-booking' ),
					'selectedFormat'   => __( 'Selected: %1$s at %2$s - %3$s', 'zeka-appointment-booking' ),
					'selectDateFirst'  => __( 'Please select a date first.', 'zeka-appointment-booking' ),
					'continueCheckout' => __( 'Continue to checkout', 'zeka-appointment-booking' ),
					'processingCheckout' => __( 'Preparing checkout...', 'zeka-appointment-booking' ),
					'verificationSent' => __( 'We sent a verification link to your email. Please confirm to finalize your free booking.', 'zeka-appointment-booking' ),
					'multiSelectedFormat' => __( 'Selected %1$d slots on %2$s', 'zeka-appointment-booking' ),
					'noSlotSelected' => __( 'Please select at least one slot.', 'zeka-appointment-booking' ),
					'localTimezoneFormat' => __( 'Times shown in your timezone: %1$s. Business timezone: %2$s.', 'zeka-appointment-booking' ),
					'businessTimezoneFormat' => __( 'Business timezone: %1$s.', 'zeka-appointment-booking' ),
				),
			)
		);
	}

	/**
	 * Get available services for public booking selection.
	 *
	 * @return array
	 */
	private static function get_services() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_services';
		$query      = "SELECT id, name, duration_minutes, price FROM {$table_name} ORDER BY id ASC";
		$results    = $wpdb->get_results( $query, ARRAY_A );

		if ( ! is_array( $results ) ) {
			return array();
		}

		return $results;
	}

	/**
	 * Format service label with duration and price/free text.
	 *
	 * @param array $service Service row.
	 * @return string
	 */
	private static function format_service_label( $service ) {
		$duration = isset( $service['duration_minutes'] ) ? absint( $service['duration_minutes'] ) : 0;
		$price    = isset( $service['price'] ) ? (float) $service['price'] : 0;
		$label    = isset( $service['name'] ) ? (string) $service['name'] : '';

		if ( $duration > 0 ) {
			$label .= ' (' . sprintf( esc_html__( '%d min', 'zeka-appointment-booking' ), $duration ) . ')';
		}

		if ( $price <= 0 ) {
			$label .= ' - ' . esc_html__( 'Free', 'zeka-appointment-booking' );
		} else {
			$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
			$label          .= ' - ' . $currency_symbol . number_format_i18n( $price, 2 );
		}

		return $label;
	}

	/**
	 * Build user-facing notice from booking result query params.
	 *
	 * @return string
	 */
	private static function get_booking_notice() {
		$status = isset( $_GET['zab_booking'] ) ? sanitize_key( wp_unslash( $_GET['zab_booking'] ) ) : '';

		if ( 'verify_sent' === $status ) {
			return __( 'Verification email sent. Please check your inbox to confirm this free booking.', 'zeka-appointment-booking' );
		}

		if ( 'verify_failed' === $status ) {
			return __( 'Verification link is invalid or expired. Please book again to receive a new verification email.', 'zeka-appointment-booking' );
		}

		if ( 'confirmed' !== $status ) {
			return '';
		}

		$appointment_id = isset( $_GET['appointment_id'] ) ? absint( wp_unslash( $_GET['appointment_id'] ) ) : 0;
		$appointment_count = isset( $_GET['appointment_count'] ) ? absint( wp_unslash( $_GET['appointment_count'] ) ) : 0;

		if ( $appointment_count > 1 ) {
			return sprintf(
				/* translators: %d: count */
				__( 'Your %d appointments are confirmed.', 'zeka-appointment-booking' ),
				$appointment_count
			);
		}

		if ( $appointment_id > 0 ) {
			return sprintf(
				/* translators: %d: appointment id */
				__( 'Your appointment is confirmed. Reference #%d.', 'zeka-appointment-booking' ),
				$appointment_id
			);
		}

		return __( 'Your appointment is confirmed.', 'zeka-appointment-booking' );
	}
}
