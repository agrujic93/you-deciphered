<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_WooCommerce {

	/**
	 * Option key for hidden booking product id.
	 */
	const PRODUCT_OPTION_KEY = 'zab_wc_product_id';
	const CONFIRMATION_PAGE_OPTION_KEY = 'zab_confirmation_page_id';

	/**
	 * Register WooCommerce hooks and AJAX actions.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_create_booking_product' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'handle_free_booking_verification' ) );
		add_action( 'wp_ajax_zab_start_checkout', array( __CLASS__, 'ajax_start_checkout' ) );
		add_action( 'wp_ajax_nopriv_zab_start_checkout', array( __CLASS__, 'ajax_start_checkout' ) );
		add_action( 'wp_ajax_zab_set_cart_timezone', array( __CLASS__, 'ajax_set_cart_timezone' ) );
		add_action( 'wp_ajax_nopriv_zab_set_cart_timezone', array( __CLASS__, 'ajax_set_cart_timezone' ) );

		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		self::release_expired_pending_locks();

		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_dynamic_service_price' ), 20 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'render_cart_item_booking_data' ), 10, 2 );
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'filter_checkout_fields' ), 20 );
		add_filter( 'woocommerce_enable_order_notes_field', array( __CLASS__, 'disable_order_notes_for_booking' ), 20 );
		add_filter( 'woocommerce_checkout_get_value', array( __CLASS__, 'prefill_checkout_values' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'add_order_line_item_booking_meta' ), 10, 4 );

		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'mark_order_appointments_confirmed' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'mark_order_appointments_confirmed' ) );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'mark_order_appointments_cancelled' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'mark_order_appointments_cancelled' ) );
		add_action( 'woocommerce_before_cart_emptied', array( __CLASS__, 'handle_booking_cart_emptied' ), 10, 1 );
		add_action( 'woocommerce_remove_cart_item', array( __CLASS__, 'handle_booking_cart_item_removed' ), 10, 2 );
		add_action( 'woocommerce_restore_cart_item', array( __CLASS__, 'handle_booking_cart_item_restored' ), 10, 2 );

		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'prevent_product_booking_mix' ), 10, 5 );
		add_filter( 'woocommerce_is_sold_individually', array( __CLASS__, 'force_booking_product_sold_individually' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( __CLASS__, 'render_booking_cart_item_quantity' ), 10, 3 );
		add_filter( 'woocommerce_update_cart_validation', array( __CLASS__, 'prevent_booking_cart_quantity_update' ), 10, 4 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'normalize_booking_cart_item_quantity' ), 10, 4 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'hide_booking_order_item_meta' ) );
		add_filter( 'gettext', array( __CLASS__, 'hide_billing_details_heading_for_booking_checkout' ), 20, 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_checkout_styles' ) );
		add_filter( 'body_class', array( __CLASS__, 'add_checkout_body_class' ) );
	}

	/**
	 * Enqueue custom styles for booking cart and checkout pages.
	 *
	 * @return void
	 */
	public static function enqueue_checkout_styles() {
		if ( ! function_exists( 'is_checkout' ) || ! function_exists( 'is_cart' ) ) {
			return;
		}

		if ( ! is_checkout() && ! is_cart() ) {
			return;
		}

		if ( is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		$style_rel_path = 'assets/css/zab-checkout.css';
		$style_abs_path = ZAB_PLUGIN_DIR . $style_rel_path;
		$style_version  = file_exists( $style_abs_path ) ? (string) filemtime( $style_abs_path ) : ZAB_PLUGIN_VERSION;

		wp_enqueue_style(
			'zab-checkout-style',
			plugins_url( $style_rel_path, ZAB_PLUGIN_FILE ),
			array(),
			$style_version
		);

		$script_rel_path = 'assets/js/zab-cart-timezone.js';
		$script_abs_path = ZAB_PLUGIN_DIR . $script_rel_path;
		$script_version  = file_exists( $script_abs_path ) ? (string) filemtime( $script_abs_path ) : ZAB_PLUGIN_VERSION;

		wp_enqueue_script(
			'zab-cart-timezone',
			plugins_url( $script_rel_path, ZAB_PLUGIN_FILE ),
			array(),
			$script_version,
			true
		);

		wp_localize_script(
			'zab-cart-timezone',
			'zabCartTimeData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'zab_cart_timezone_nonce' ),
			)
		);
	}

	/**
	 * Persist visitor timezone to booking cart items for server-side local-time rendering.
	 *
	 * @return void
	 */
	public static function ajax_set_cart_timezone() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'zab_cart_timezone_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'zeka-appointment-booking' ) ), 403 );
		}

		$browser_tz = isset( $_POST['browser_tz'] ) ? sanitize_text_field( wp_unslash( $_POST['browser_tz'] ) ) : '';

		if ( '' === $browser_tz ) {
			wp_send_json_error( array( 'message' => __( 'Missing timezone.', 'zeka-appointment-booking' ) ), 400 );
		}

		try {
			new DateTimeZone( $browser_tz );
		} catch ( Exception $exception ) {
			wp_send_json_error( array( 'message' => __( 'Invalid timezone.', 'zeka-appointment-booking' ) ), 400 );
		}

		if ( null === WC()->cart ) {
			wc_load_cart();
		}

		if ( null === WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Cart is not available right now.', 'zeka-appointment-booking' ) ), 500 );
		}

		$changed = false;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['zab_appointment_id'] ) ) {
				continue;
			}

			$current_tz = isset( $cart_item['zab_visitor_tz'] ) ? (string) $cart_item['zab_visitor_tz'] : '';

			if ( $current_tz === $browser_tz ) {
				continue;
			}

			WC()->cart->cart_contents[ $cart_item_key ]['zab_visitor_tz'] = $browser_tz;
			$changed = true;
		}

		if ( $changed ) {
			WC()->cart->set_session();
		}

		wp_send_json_success(
			array(
				'updated' => $changed,
			)
		);
	}

	/**
	 * Add body class for custom booking cart/checkout styling.
	 *
	 * @param array $classes Existing body classes.
	 * @return array Modified body classes.
	 */
	public static function add_checkout_body_class( $classes ) {
		if ( ! function_exists( 'is_checkout' ) || ! function_exists( 'is_cart' ) ) {
			return $classes;
		}

		$is_booking_checkout = is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && self::cart_has_booking_item();
		$is_booking_cart     = is_cart() && self::cart_has_booking_item();

		if ( $is_booking_checkout || $is_booking_cart ) {
			$classes[] = 'zab-booking-active';
		}
		return $classes;
	}

	/**
	 * Ensure hidden booking product exists when WooCommerce is active.
	 *
	 * @return void
	 */
	public static function maybe_create_booking_product() {
		if ( ! self::is_woocommerce_active() || ! class_exists( 'WC_Product_Simple' ) ) {
			return;
		}

		$product_id = (int) get_option( self::PRODUCT_OPTION_KEY, 0 );

		if ( $product_id > 0 ) {
			$product = wc_get_product( $product_id );
			if ( $product instanceof WC_Product ) {
				return;
			}
		}

		$product_id = self::create_hidden_booking_product();

		if ( $product_id > 0 ) {
			update_option( self::PRODUCT_OPTION_KEY, $product_id );
		}
	}

	/**
	 * AJAX entry-point: lock slot, put booking product in cart, return checkout URL.
	 *
	 * @return void
	 */
	public static function ajax_start_checkout() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'zab_booking_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'zeka-appointment-booking' ) ), 403 );
		}

		if ( ! self::is_woocommerce_active() ) {
			wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'zeka-appointment-booking' ) ), 400 );
		}

		self::release_expired_pending_locks();

		$date        = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$start_time  = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
		$end_time    = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
		$slots_raw   = isset( $_POST['slots'] ) ? wp_unslash( $_POST['slots'] ) : '';
		$service_id  = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;
		$first_name  = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name   = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$browser_tz  = isset( $_POST['browser_tz'] ) ? sanitize_text_field( wp_unslash( $_POST['browser_tz'] ) ) : '';
		$identity    = self::resolve_booking_identity( $first_name, $last_name, $email );
		$first_name  = $identity['first_name'];
		$last_name   = $identity['last_name'];
		$email       = $identity['email'];

		if ( '' !== $browser_tz ) {
			try {
				new DateTimeZone( $browser_tz );
			} catch ( Exception $exception ) {
				$browser_tz = '';
			}
		}

		if ( ! self::is_valid_date( $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking data.', 'zeka-appointment-booking' ) ), 400 );
		}

		$requested_slots = self::build_requested_slots( $slots_raw, $start_time, $end_time );

		if ( empty( $requested_slots ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select at least one slot.', 'zeka-appointment-booking' ) ), 400 );
		}

		$service = self::resolve_booking_service( $service_id );

		if ( empty( $service ) ) {
			wp_send_json_error( array( 'message' => __( 'No booking services are configured yet. Please add at least one service in admin.', 'zeka-appointment-booking' ) ), 400 );
		}

		$service_id = (int) $service['id'];

		// Get existing appointments from cart to cancel them (for replacement).
		$existing_appointment_ids = self::get_cart_appointment_ids();

		$appointment_ids = array();
		$slot_payloads   = array();

		foreach ( $requested_slots as $slot ) {
			$slot_start = $slot['start'];
			$slot_end   = $slot['end'];

			if ( ! self::is_valid_time( $slot_start ) || ! self::is_valid_time( $slot_end ) || ! self::is_valid_time_range( $slot_start, $slot_end ) ) {
				self::cancel_appointments( $appointment_ids );
				wp_send_json_error( array( 'message' => __( 'Invalid slot time range.', 'zeka-appointment-booking' ) ), 400 );
			}

			if ( ! self::is_slot_currently_available( $date, $slot_start, $slot_end, $service_id ) ) {
				self::cancel_appointments( $appointment_ids );
				wp_send_json_error( array( 'message' => __( 'One or more selected slots are no longer available. Please refresh and choose again.', 'zeka-appointment-booking' ) ), 409 );
			}

			$utc_slot = self::to_utc_slot_range( $date, $slot_start, $slot_end );

			if ( empty( $utc_slot ) ) {
				self::cancel_appointments( $appointment_ids );
				wp_send_json_error( array( 'message' => __( 'Unable to process selected time slots.', 'zeka-appointment-booking' ) ), 400 );
			}

			if ( self::has_slot_conflict( $utc_slot['start'], $utc_slot['end'] ) ) {
				self::cancel_appointments( $appointment_ids );
				wp_send_json_error( array( 'message' => __( 'One or more selected slots are no longer available. Please refresh and choose again.', 'zeka-appointment-booking' ) ), 409 );
			}

			$appointment_id = self::create_pending_appointment(
				array(
					'service_id'   => (int) $service['id'],
					'booking_date' => $date,
					'start_utc'    => $utc_slot['start'],
					'end_utc'      => $utc_slot['end'],
				)
			);

			if ( $appointment_id < 1 ) {
				self::cancel_appointments( $appointment_ids );
				wp_send_json_error( array( 'message' => __( 'Could not lock selected slots. Please try again.', 'zeka-appointment-booking' ) ), 500 );
			}

			$appointment_ids[] = $appointment_id;
			$slot_payloads[]   = array(
				'appointment_id' => $appointment_id,
				'start'          => $slot_start,
				'end'            => $slot_end,
				'start_utc'      => $utc_slot['start'],
				'end_utc'        => $utc_slot['end'],
			);
		}

		$primary_appointment_id = (int) $appointment_ids[0];

		$product_id = self::get_or_create_booking_product_id();

		if ( $product_id < 1 ) {
			self::cancel_appointments( $appointment_ids );
			wp_send_json_error( array( 'message' => __( 'Booking product is not available.', 'zeka-appointment-booking' ) ), 500 );
		}

		if ( null === WC()->cart ) {
			wc_load_cart();
		}

		if ( null === WC()->cart ) {
			self::cancel_appointments( $appointment_ids );
			wp_send_json_error( array( 'message' => __( 'Cart is not available right now.', 'zeka-appointment-booking' ) ), 500 );
		}

		// Cancel existing appointments (they're being replaced by new ones).
		self::cancel_appointments( $existing_appointment_ids );

		WC()->cart->empty_cart();

		foreach ( $slot_payloads as $slot_payload ) {
			$cart_item_data = array(
				'zab_appointment_id' => (int) $slot_payload['appointment_id'],
				'zab_service_id'     => (int) $service['id'],
				'zab_service_name'   => (string) $service['name'],
				'zab_date'           => $date,
				'zab_start'          => $slot_payload['start'],
				'zab_end'            => $slot_payload['end'],
				'zab_start_utc'      => $slot_payload['start_utc'],
				'zab_end_utc'        => $slot_payload['end_utc'],
				'zab_visitor_tz'     => $browser_tz,
				'zab_first_name'     => $first_name,
				'zab_last_name'      => $last_name,
				'zab_email'          => $email,
				'zab_unique_key'     => wp_generate_uuid4(),
			);

			$added = WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );

			if ( false === $added ) {
				self::cancel_appointments( $appointment_ids );
				WC()->cart->empty_cart();
				wp_send_json_error( array( 'message' => __( 'Could not add booking to cart.', 'zeka-appointment-booking' ) ), 500 );
			}
		}

		wp_send_json_success(
			array(
				'appointment_id' => $primary_appointment_id,
				'appointment_count' => count( $appointment_ids ),
				'checkout_url'   => wc_get_checkout_url(),
			)
		);
	}

	/**
	 * Apply service price to booking line item before totals are calculated.
	 *
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public static function apply_dynamic_service_price( $cart ) {
		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['zab_service_id'] ) || empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
				continue;
			}

			$service = self::get_service( (int) $cart_item['zab_service_id'] );

			if ( empty( $service ) || ! isset( $service['price'] ) ) {
				continue;
			}

			$price = (float) $service['price'];
			$cart->cart_contents[ $cart_item_key ]['data']->set_price( $price );
		}
	}

	/**
	 * Show booking data in cart/checkout items.
	 *
	 * @param array $item_data Existing item data.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function render_cart_item_booking_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['zab_service_name'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Service', 'zeka-appointment-booking' ),
				'value' => wc_clean( $cart_item['zab_service_name'] ),
			);
		}

		if ( ! empty( $cart_item['zab_date'] ) && ! empty( $cart_item['zab_start'] ) && ! empty( $cart_item['zab_end'] ) ) {
			$start_utc = isset( $cart_item['zab_start_utc'] ) ? (string) $cart_item['zab_start_utc'] : '';
			$end_utc   = isset( $cart_item['zab_end_utc'] ) ? (string) $cart_item['zab_end_utc'] : '';
			$visitor_tz = isset( $cart_item['zab_visitor_tz'] ) ? (string) $cart_item['zab_visitor_tz'] : '';

			if ( '' === $visitor_tz && isset( $_COOKIE['zab_browser_tz'] ) ) {
				$visitor_tz = sanitize_text_field( wp_unslash( $_COOKIE['zab_browser_tz'] ) );
			}

			if ( '' !== $visitor_tz ) {
				try {
					new DateTimeZone( $visitor_tz );
				} catch ( Exception $exception ) {
					$visitor_tz = '';
				}
			}

			if ( '' === $start_utc || '' === $end_utc ) {
				$utc_slot = self::to_utc_slot_range(
					(string) $cart_item['zab_date'],
					(string) $cart_item['zab_start'],
					(string) $cart_item['zab_end']
				);

				if ( is_array( $utc_slot ) ) {
					$start_utc = isset( $utc_slot['start'] ) ? (string) $utc_slot['start'] : '';
					$end_utc   = isset( $utc_slot['end'] ) ? (string) $utc_slot['end'] : '';
				}
			}

			$appointment_value = (string) $cart_item['zab_date'] . ' ' . (string) $cart_item['zab_start'] . ' - ' . (string) $cart_item['zab_end'] . ' (' . ZAB_Time::business_timezone_string() . ')';

			if ( '' !== $start_utc && '' !== $end_utc && '' !== $visitor_tz ) {
				try {
					$visitor_timezone = new DateTimeZone( $visitor_tz );
					$start_local      = new DateTimeImmutable( $start_utc, new DateTimeZone( 'UTC' ) );
					$end_local        = new DateTimeImmutable( $end_utc, new DateTimeZone( 'UTC' ) );
					$start_local      = $start_local->setTimezone( $visitor_timezone );
					$end_local        = $end_local->setTimezone( $visitor_timezone );

					$appointment_value .= ' / ' . $start_local->format( 'Y-m-d H:i' ) . ' - ' . $end_local->format( 'H:i' ) . ' (' . __( 'Your local time', 'zeka-appointment-booking' ) . ')';
				} catch ( Exception $exception ) {
					// Keep business timezone value only when visitor timezone is invalid.
				}
			}

			$item_data[] = array(
				'key'   => __( 'Appointment', 'zeka-appointment-booking' ),
				'value' => wc_clean( $appointment_value ),
			);
		}

		return $item_data;
	}

	/**
	 * Reduce checkout fields for booking product orders.
	 *
	 * @param array $fields Checkout fields.
	 * @return array
	 */
	public static function filter_checkout_fields( $fields ) {
		if ( ! self::cart_has_booking_item() ) {
			return $fields;
		}

		// Rebuild billing section with only identity fields so classic checkout renders them.
		$fields['billing'] = array(
			'billing_first_name' => array(
				'type'     => 'text',
				'label'    => __( 'First name', 'zeka-appointment-booking' ),
				'required' => true,
			),
			'billing_last_name'  => array(
				'type'     => 'text',
				'label'    => __( 'Last name', 'zeka-appointment-booking' ),
				'required' => true,
			),
			'billing_email'      => array(
				'type'     => 'email',
				'label'    => __( 'Email address', 'zeka-appointment-booking' ),
				'required' => true,
			),
		);

		// Remove custom contact section to avoid duplicate/invisible groups.
		unset( $fields['contact'] );

		// Remove shipping section for booking checkout.
		unset( $fields['shipping'] );

		return $fields;
	}

	/**
	 * Hide "Billing details" heading on checkout for booking carts.
	 *
	 * @param string $translated_text Translated text.
	 * @param string $text Original text.
	 * @param string $domain Text domain.
	 * @return string
	 */
	public static function hide_billing_details_heading_for_booking_checkout( $translated_text, $text, $domain ) {
		if ( 'woocommerce' !== $domain || 'Billing details' !== $text ) {
			return $translated_text;
		}

		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_admin() ) {
			return $translated_text;
		}

		if ( ! self::cart_has_booking_item() ) {
			return $translated_text;
		}

		return '';
	}

	/**
	 * Disable order notes for booking-only checkout.
	 *
	 * @param bool $enabled Default flag.
	 * @return bool
	 */
	public static function disable_order_notes_for_booking( $enabled ) {
		if ( self::cart_has_booking_item() ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Prefill checkout identity fields from booking form.
	 *
	 * @param mixed  $value Current value.
	 * @param string $input Field key.
	 * @return mixed
	 */
	public static function prefill_checkout_values( $value, $input ) {
		if ( ! self::cart_has_booking_item() ) {
			return $value;
		}

		if ( null === WC()->cart ) {
			return $value;
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( empty( $item['zab_appointment_id'] ) ) {
				continue;
			}

			if ( 'billing_first_name' === $input && ! empty( $item['zab_first_name'] ) ) {
				return $item['zab_first_name'];
			}

			if ( 'billing_last_name' === $input && ! empty( $item['zab_last_name'] ) ) {
				return $item['zab_last_name'];
			}

			if ( 'billing_email' === $input && ! empty( $item['zab_email'] ) ) {
				return $item['zab_email'];
			}

			// Also support contact section field names.
			if ( in_array( $input, array( 'billing_first_name', 'billing_last_name', 'billing_email' ), true ) ) {
				continue;
			}
		}

		return $value;
	}

	/**
	 * Persist booking meta to order line item and bind appointment to order.
	 *
	 * @param WC_Order_Item_Product $item Order line item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values Cart item values.
	 * @param WC_Order              $order Order object.
	 * @return void
	 */
	public static function add_order_line_item_booking_meta( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['zab_appointment_id'] ) ) {
			return;
		}

		$appointment_id = absint( $values['zab_appointment_id'] );

		$item->add_meta_data( 'appointment_id', $appointment_id, true );

		if ( ! empty( $values['zab_service_id'] ) ) {
			$item->add_meta_data( 'service_id', absint( $values['zab_service_id'] ), true );
		}

		if ( ! empty( $values['zab_date'] ) ) {
			$item->add_meta_data( 'booking_date', sanitize_text_field( $values['zab_date'] ), true );
		}

		if ( ! empty( $values['zab_start'] ) ) {
			$item->add_meta_data( 'booking_start', sanitize_text_field( $values['zab_start'] ), true );
		}

		if ( ! empty( $values['zab_end'] ) ) {
			$item->add_meta_data( 'booking_end', sanitize_text_field( $values['zab_end'] ), true );
		}

		self::set_appointment_status( $appointment_id, 'pending', $order->get_id() );
	}

	/**
	 * Confirm appointments attached to order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return void
	 */
	public static function mark_order_appointments_confirmed( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$appointment_ids = self::get_order_appointment_ids( $order_id );

		if ( empty( $appointment_ids ) ) {
			return;
		}

		$free_appointment_ids = array();
		$paid_appointment_ids = array();

		foreach ( $appointment_ids as $appointment_id ) {
			self::sync_customer_from_order( $appointment_id, $order_id );

			if ( self::is_free_appointment( $appointment_id ) ) {
				$free_appointment_ids[] = $appointment_id;
				continue;
			}

			$paid_appointment_ids[] = $appointment_id;
		}

		foreach ( $paid_appointment_ids as $appointment_id ) {
			self::set_appointment_status( $appointment_id, 'confirmed', $order_id );
			do_action( 'zab_appointment_confirmed', $appointment_id );
		}

		if ( empty( $free_appointment_ids ) ) {
			return;
		}

		foreach ( $free_appointment_ids as $appointment_id ) {
			self::set_appointment_status( $appointment_id, 'awaiting_verification', $order_id );
			self::set_appointment_lock_expiration( $appointment_id, self::get_free_verification_expiry_utc() );
		}

		if ( self::has_sent_free_verification_for_order( $order_id ) ) {
			return;
		}

		$email = sanitize_email( $order->get_billing_email() );
		if ( empty( $email ) ) {
			return;
		}

		$first_name = sanitize_text_field( $order->get_billing_first_name() );
		if ( '' === $first_name ) {
			$first_name = __( 'Customer', 'zeka-appointment-booking' );
		}

		if ( self::send_free_booking_verification_email( $free_appointment_ids, $email, $first_name ) ) {
			self::mark_free_verification_sent_for_order( $order_id );
		}
	}

	/**
	 * Cancel appointments attached to order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return void
	 */
	public static function mark_order_appointments_cancelled( $order_id ) {
		$appointment_ids = self::get_order_appointment_ids( $order_id );

		foreach ( $appointment_ids as $appointment_id ) {
			if ( self::should_skip_order_cancellation( $appointment_id, $order_id ) ) {
				continue;
			}

			self::set_appointment_status( $appointment_id, 'cancelled', $order_id );
			do_action( 'zab_appointment_cancelled', $appointment_id );
		}
	}

	/**
	 * Determine whether order cancellation should skip appointment status update.
	 *
	 * @param int $appointment_id Appointment id.
	 * @param int $order_id Triggering Woo order id.
	 * @return bool
	 */
	private static function should_skip_order_cancellation( $appointment_id, $order_id ) {
		$appointment_id = absint( $appointment_id );
		$order_id       = absint( $order_id );

		if ( $appointment_id < 1 || $order_id < 1 ) {
			return true;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$appointment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT status, order_id FROM {$table_name} WHERE id = %d LIMIT 1",
				$appointment_id
			)
		);

		if ( ! $appointment ) {
			return true;
		}

		$current_status   = sanitize_key( (string) $appointment->status );
		$current_order_id = absint( $appointment->order_id );

		// Preserve manual admin confirmations that were explicitly detached from order flow.
		if ( 'confirmed' === $current_status && 0 === $current_order_id ) {
			return true;
		}

		// Skip if appointment is no longer linked to this order.
		if ( $current_order_id > 0 && $current_order_id !== $order_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Return current booking selection from cart to restore frontend widget state.
	 *
	 * @return array
	 */
	public static function get_cart_booking_state() {
		if ( ! self::is_woocommerce_active() ) {
			return array();
		}

		if ( null === WC()->cart ) {
			wc_load_cart();
		}

		if ( null === WC()->cart ) {
			return array();
		}

		$date       = '';
		$service_id = 0;
		$slots      = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( empty( $item['zab_appointment_id'] ) || empty( $item['zab_date'] ) || empty( $item['zab_start'] ) || empty( $item['zab_end'] ) ) {
				continue;
			}

			$item_date       = sanitize_text_field( $item['zab_date'] );
			$item_service_id = ! empty( $item['zab_service_id'] ) ? absint( $item['zab_service_id'] ) : 0;

			if ( '' === $date ) {
				$date       = $item_date;
				$service_id = $item_service_id;
			}

			if ( $item_date !== $date || $item_service_id !== $service_id ) {
				continue;
			}

			$slots[] = array(
				'start' => sanitize_text_field( $item['zab_start'] ),
				'end'   => sanitize_text_field( $item['zab_end'] ),
			);

			$last_index = count( $slots ) - 1;
			$utc_range  = self::to_utc_slot_range( $item_date, $slots[ $last_index ]['start'], $slots[ $last_index ]['end'] );

			if ( ! empty( $utc_range ) ) {
				$slots[ $last_index ]['utc_start'] = $utc_range['start'];
				$slots[ $last_index ]['utc_end']   = $utc_range['end'];
			}

			$slots[ $last_index ]['site_date']     = $item_date;
			$slots[ $last_index ]['site_timezone'] = ZAB_Time::business_timezone_string();
		}

		if ( '' === $date || empty( $slots ) ) {
			return array();
		}

		$deduped = array();
		foreach ( $slots as $slot ) {
			$key = $slot['start'] . '|' . $slot['end'];
			$deduped[ $key ] = $slot;
		}

		$slots = array_values( $deduped );

		usort(
			$slots,
			static function ( $a, $b ) {
				return strcmp( $a['start'], $b['start'] );
			}
		);

		return array(
			'date'      => $date,
			'serviceId' => $service_id,
			'siteTimezone' => ZAB_Time::business_timezone_string(),
			'slots'     => $slots,
		);
	}

	/**
	 * Get unique appointment IDs from cart items.
	 *
	 * @return array
	 */
	private static function get_cart_appointment_ids() {
		if ( ! self::is_woocommerce_active() || null === WC()->cart ) {
			return array();
		}

		$appointment_ids = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			$appointment_id = absint( isset( $item['zab_appointment_id'] ) ? $item['zab_appointment_id'] : 0 );
			if ( $appointment_id > 0 ) {
				$appointment_ids[] = $appointment_id;
			}
		}

		return array_values( array_unique( $appointment_ids ) );
	}

	/**
	 * Get unique appointment IDs from order items.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return array
	 */
	private static function get_order_appointment_ids( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return array();
		}

		$appointment_ids = array();

		foreach ( $order->get_items() as $item ) {
			$appointment_id = absint( $item->get_meta( 'appointment_id', true ) );
			if ( $appointment_id > 0 ) {
				$appointment_ids[] = $appointment_id;
			}
		}

		return array_values( array_unique( $appointment_ids ) );
	}

	/**
	 * Check if appointment belongs to a free service.
	 *
	 * @param int $appointment_id Appointment id.
	 * @return bool
	 */
	private static function is_free_appointment( $appointment_id ) {
		$appointment_id = absint( $appointment_id );

		if ( $appointment_id < 1 ) {
			return false;
		}

		global $wpdb;

		$appointments_table = $wpdb->prefix . 'booking_appointments';
		$services_table     = $wpdb->prefix . 'booking_services';
		$price              = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT s.price FROM {$appointments_table} a INNER JOIN {$services_table} s ON s.id = a.service_id WHERE a.id = %d LIMIT 1",
				$appointment_id
			)
		);

		return null !== $price && (float) $price <= 0;
	}

	/**
	 * Check if free verification email has already been sent for this order.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return bool
	 */
	private static function has_sent_free_verification_for_order( $order_id ) {
		return '1' === get_post_meta( absint( $order_id ), '_zab_free_verification_sent', true );
	}

	/**
	 * Mark free verification email as sent for this order.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return void
	 */
	private static function mark_free_verification_sent_for_order( $order_id ) {
		update_post_meta( absint( $order_id ), '_zab_free_verification_sent', '1' );
	}

	/**
	 * Sync customer data from WooCommerce order to booking_customers table.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @param int $order_id WooCommerce order ID.
	 * @return void
	 */
	private static function sync_customer_from_order( $appointment_id, $order_id ) {
		if ( $appointment_id < 1 || $order_id < 1 ) {
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		global $wpdb;

		$first_name = sanitize_text_field( $order->get_billing_first_name() );
		$last_name  = sanitize_text_field( $order->get_billing_last_name() );
		$email      = sanitize_email( $order->get_billing_email() );
		$phone      = sanitize_text_field( $order->get_billing_phone() );

		if ( empty( $first_name ) || empty( $email ) ) {
			return;
		}

		$customers_table = $wpdb->prefix . 'booking_customers';

		// Check if customer already exists.
		$existing_customer = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$customers_table} WHERE email = %s LIMIT 1",
				$email
			)
		);

		if ( $existing_customer ) {
			$customer_id = absint( $existing_customer );

			// Refresh existing customer profile with latest checkout values.
			$wpdb->update(
				$customers_table,
				array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'phone'      => $phone,
				),
				array( 'id' => $customer_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			// Create new customer record.
			$inserted = $wpdb->insert(
				$customers_table,
				array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'email'      => $email,
					'phone'      => $phone,
				),
				array( '%s', '%s', '%s', '%s' )
			);

			if ( false === $inserted ) {
				return;
			}

			$customer_id = (int) $wpdb->insert_id;
		}

		// Link customer to appointment.
		if ( $customer_id > 0 ) {
			$appointments_table = $wpdb->prefix . 'booking_appointments';

			$wpdb->update(
				$appointments_table,
				array( 'customer_id' => $customer_id ),
				array( 'id' => $appointment_id ),
				array( '%d' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Get or create the hidden booking product id.
	 *
	 * @return int
	 */
	private static function get_or_create_booking_product_id() {
		$product_id = (int) get_option( self::PRODUCT_OPTION_KEY, 0 );

		if ( $product_id > 0 && wc_get_product( $product_id ) instanceof WC_Product ) {
			return $product_id;
		}

		self::maybe_create_booking_product();

		$product_id = (int) get_option( self::PRODUCT_OPTION_KEY, 0 );

		if ( $product_id > 0 && wc_get_product( $product_id ) instanceof WC_Product ) {
			return $product_id;
		}

		return 0;
	}

	/**
	 * Create hidden virtual WooCommerce product used for booking checkout.
	 *
	 * @return int
	 */
	private static function create_hidden_booking_product() {
		$existing_id = wc_get_product_id_by_sku( 'zab-booking-service' );

		if ( $existing_id > 0 ) {
			return (int) $existing_id;
		}

		$product = new WC_Product_Simple();
		$product->set_name( __( 'Booking Service', 'zeka-appointment-booking' ) );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_virtual( true );
		$product->set_regular_price( '0' );
		$product->set_sku( 'zab-booking-service' );
		$product->set_description( __( 'Auto-generated product for appointment bookings.', 'zeka-appointment-booking' ) );

		$product_id = $product->save();

		return $product_id > 0 ? (int) $product_id : 0;
	}

	/**
	 * Read service row.
	 *
	 * @param int $service_id Service ID.
	 * @return array
	 */
	private static function get_service( $service_id ) {
		if ( $service_id < 1 ) {
			return array();
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_services';
		$query      = $wpdb->prepare(
			"SELECT id, name, duration_minutes, price FROM {$table_name} WHERE id = %d LIMIT 1",
			$service_id
		);

		$service = $wpdb->get_row( $query, ARRAY_A );

		return is_array( $service ) ? $service : array();
	}

	/**
	 * Resolve booking service from request id or fallback to first available service.
	 *
	 * @param int $service_id Requested service id.
	 * @return array
	 */
	private static function resolve_booking_service( $service_id ) {
		$service = self::get_service( $service_id );

		if ( ! empty( $service ) ) {
			return $service;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_services';
		$query      = "SELECT id, name, duration_minutes, price FROM {$table_name} ORDER BY id ASC LIMIT 1";
		$fallback   = $wpdb->get_row( $query, ARRAY_A );

		return is_array( $fallback ) ? $fallback : array();
	}

	/**
	 * Convert local date/time slot to UTC datetime strings.
	 *
	 * @param string $date Local date Y-m-d.
	 * @param string $start_time Start HH:MM.
	 * @param string $end_time End HH:MM.
	 * @return array
	 */
	private static function to_utc_slot_range( $date, $start_time, $end_time ) {
		$timezone      = ZAB_Time::business_timezone();
		$utc_timezone  = new DateTimeZone( 'UTC' );
		$start_local   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $start_time, $timezone );
		$end_local     = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $end_time, $timezone );

		if ( false === $start_local || false === $end_local || $end_local <= $start_local ) {
			return array();
		}

		return array(
			'start' => $start_local->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
			'end'   => $end_local->setTimezone( $utc_timezone )->format( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Detect if selected slot conflicts with pending/confirmed appointments.
	 *
	 * @param string $start_utc UTC datetime start.
	 * @param string $end_utc UTC datetime end.
	 * @return bool
	 */
	private static function has_slot_conflict( $start_utc, $end_utc ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$now_utc    = current_time( 'mysql', true );
		$query      = $wpdb->prepare(
			"SELECT COUNT(id) FROM {$table_name} WHERE ( status = 'confirmed' OR ( status = 'pending' AND ( order_id > 0 OR ( lock_expires_at IS NOT NULL AND lock_expires_at > %s ) ) ) OR ( status = 'awaiting_verification' AND lock_expires_at IS NOT NULL AND lock_expires_at > %s ) ) AND start_time < %s AND end_time > %s",
			$now_utc,
			$now_utc,
			$end_utc,
			$start_utc
		);

		$count = (int) $wpdb->get_var( $query );

		return $count > 0;
	}

	/**
	 * Create pending appointment row.
	 *
	 * @param array $data Appointment fields.
	 * @return int
	 */
	private static function create_pending_appointment( $data ) {
		global $wpdb;

		$table_name       = $wpdb->prefix . 'booking_appointments';
		$lock_expires_at  = self::get_lock_expiry_utc();
		$inserted         = $wpdb->insert(
			$table_name,
			array(
				'service_id'      => (int) $data['service_id'],
				'customer_id'     => 0,
				'order_id'        => 0,
				'booking_date'    => $data['booking_date'],
				'start_time'      => $data['start_utc'],
				'end_time'        => $data['end_utc'],
				'lock_expires_at' => $lock_expires_at,
				'status'          => 'pending',
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update appointment status and order relation.
	 *
	 * @param int    $appointment_id Appointment id.
	 * @param string $status New status.
	 * @param int    $order_id Order id.
	 * @return void
	 */
	private static function set_appointment_status( $appointment_id, $status, $order_id ) {
		$appointment_id = absint( $appointment_id );
		$order_id       = absint( $order_id );
		$status         = sanitize_key( $status );

		if ( $appointment_id < 1 ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$data       = array(
			'status' => $status,
		);
		$formats    = array( '%s' );

		if ( $order_id > 0 ) {
			$data['order_id'] = $order_id;
			$formats[]        = '%d';
		}

		if ( 'pending' !== $status ) {
			$data['lock_expires_at'] = null;
			$formats[]               = '%s';
		}

		$wpdb->update(
			$table_name,
			$data,
			array( 'id' => $appointment_id ),
			$formats,
			array( '%d' )
		);
	}

	/**
	 * Ensure requested slot is still in generated availability output.
	 *
	 * @param string $date Booking date Y-m-d.
	 * @param string $start_time Slot start HH:MM.
	 * @param string $end_time Slot end HH:MM.
	 * @param int    $service_id Service id.
	 * @return bool
	 */
	private static function is_slot_currently_available( $date, $start_time, $end_time, $service_id ) {
		$available_slots = ZAB_Availability::get_available_slots_for_date( $date, $service_id );

		foreach ( $available_slots as $slot ) {
			if ( isset( $slot['start'], $slot['end'] ) && $slot['start'] === $start_time && $slot['end'] === $end_time ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validate date string in Y-m-d format.
	 *
	 * @param string $date Date value.
	 * @return bool
	 */
	private static function is_valid_date( $date ) {
		if ( ! is_string( $date ) ) {
			return false;
		}

		$date_object = DateTimeImmutable::createFromFormat( 'Y-m-d', $date );

		return $date_object && $date_object->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Validate time string in HH:MM format.
	 *
	 * @param string $time Time value.
	 * @return bool
	 */
	private static function is_valid_time( $time ) {
		return is_string( $time ) && 1 === preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time );
	}

	/**
	 * Check time range order.
	 *
	 * @param string $start_time Start time HH:MM.
	 * @param string $end_time End time HH:MM.
	 * @return bool
	 */
	private static function is_valid_time_range( $start_time, $end_time ) {
		$start = strtotime( '1970-01-01 ' . $start_time . ':00 UTC' );
		$end   = strtotime( '1970-01-01 ' . $end_time . ':00 UTC' );

		if ( false === $start || false === $end ) {
			return false;
		}

		return $end > $start;
	}

	/**
	 * Build requested slots array from JSON payload with single-slot fallback.
	 *
	 * @param mixed  $slots_raw Raw JSON slots payload.
	 * @param string $fallback_start Fallback start time.
	 * @param string $fallback_end Fallback end time.
	 * @return array
	 */
	private static function build_requested_slots( $slots_raw, $fallback_start, $fallback_end ) {
		$slots = array();

		if ( is_string( $slots_raw ) && '' !== $slots_raw ) {
			$decoded = json_decode( $slots_raw, true );

			if ( is_array( $decoded ) ) {
				foreach ( $decoded as $slot ) {
					if ( ! is_array( $slot ) || empty( $slot['start'] ) || empty( $slot['end'] ) ) {
						continue;
					}

					$slots[] = array(
						'start' => sanitize_text_field( $slot['start'] ),
						'end'   => sanitize_text_field( $slot['end'] ),
					);
				}
			}
		}

		if ( empty( $slots ) && ! empty( $fallback_start ) && ! empty( $fallback_end ) ) {
			$slots[] = array(
				'start' => $fallback_start,
				'end'   => $fallback_end,
			);
		}

		$deduped = array();
		foreach ( $slots as $slot ) {
			$key = $slot['start'] . '|' . $slot['end'];
			$deduped[ $key ] = $slot;
		}

		$slots = array_values( $deduped );

		if ( count( $slots ) > 12 ) {
			$slots = array_slice( $slots, 0, 12 );
		}

		return $slots;
	}

	/**
	 * Cancel pending appointments by id list.
	 *
	 * @param array $appointment_ids Appointment ids.
	 * @return void
	 */
	private static function cancel_appointments( $appointment_ids ) {
		if ( ! is_array( $appointment_ids ) ) {
			return;
		}

		foreach ( $appointment_ids as $appointment_id ) {
			self::set_appointment_status( (int) $appointment_id, 'cancelled', 0 );
		}
	}

	/**
	 * Release expired pending locks without order assignment.
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

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table_name} SET status = 'cancelled' WHERE status = 'awaiting_verification' AND ( lock_expires_at IS NULL OR lock_expires_at <= %s )",
				$now_utc
			)
		);
	}

	/**
	 * Build UTC expiration timestamp for free verification window.
	 *
	 * @return string
	 */
	private static function get_free_verification_expiry_utc() {
		$now_utc = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );

		return $now_utc->modify( '+1 hour' )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Set appointment lock expiry timestamp.
	 *
	 * @param int    $appointment_id Appointment id.
	 * @param string $expires_utc UTC datetime string.
	 * @return void
	 */
	private static function set_appointment_lock_expiration( $appointment_id, $expires_utc ) {
		$appointment_id = absint( $appointment_id );

		if ( $appointment_id < 1 || empty( $expires_utc ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$wpdb->update(
			$table_name,
			array( 'lock_expires_at' => $expires_utc ),
			array( 'id' => $appointment_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Return pending lock TTL in minutes.
	 *
	 * @return int
	 */
	private static function get_lock_ttl_minutes() {
		$hold_stock = absint( get_option( 'woocommerce_hold_stock_minutes', 0 ) );

		if ( $hold_stock > 0 ) {
			return $hold_stock;
		}

		return 15;
	}

	/**
	 * Build UTC lock expiration timestamp.
	 *
	 * @return string
	 */
	private static function get_lock_expiry_utc() {
		$now_utc = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		$ttl     = self::get_lock_ttl_minutes();

		return $now_utc->modify( '+' . $ttl . ' minutes' )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Upsert customer row and return id.
	 *
	 * @param string $first_name First name.
	 * @param string $last_name Last name.
	 * @param string $email Email.
	 * @return int
	 */
	private static function upsert_customer( $first_name, $last_name, $email ) {
		global $wpdb;

		$customers_table = $wpdb->prefix . 'booking_customers';
		$existing_id     = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$customers_table} WHERE email = %s LIMIT 1", $email )
		);

		if ( $existing_id > 0 ) {
			$wpdb->update(
				$customers_table,
				array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
				),
				array( 'id' => $existing_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			return $existing_id;
		}

		$inserted = $wpdb->insert(
			$customers_table,
			array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'email'      => $email,
			),
			array( '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Resolve booking identity from request values and logged-in user fallback.
	 *
	 * @param string $first_name Requested first name.
	 * @param string $last_name Requested last name.
	 * @param string $email Requested email.
	 * @return array
	 */
	private static function resolve_booking_identity( $first_name, $last_name, $email ) {
		$first_name = sanitize_text_field( (string) $first_name );
		$last_name  = sanitize_text_field( (string) $last_name );
		$email      = sanitize_email( (string) $email );

		if ( ! empty( $first_name ) && ! empty( $last_name ) && ! empty( $email ) ) {
			return array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'email'      => $email,
			);
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( $user instanceof WP_User ) {
				if ( empty( $email ) && ! empty( $user->user_email ) ) {
					$email = sanitize_email( $user->user_email );
				}

				if ( empty( $first_name ) && ! empty( $user->first_name ) ) {
					$first_name = sanitize_text_field( $user->first_name );
				}

				if ( empty( $last_name ) && ! empty( $user->last_name ) ) {
					$last_name = sanitize_text_field( $user->last_name );
				}

				if ( empty( $first_name ) && ! empty( $user->display_name ) ) {
					$first_name = sanitize_text_field( $user->display_name );
				}
			}
		}

		if ( empty( $first_name ) && ! empty( $email ) ) {
			$email_name = current( explode( '@', $email ) );
			$first_name = sanitize_text_field( (string) $email_name );
		}

		return array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
		);
	}

	/**
	 * Link customer to appointment.
	 *
	 * @param int $appointment_id Appointment id.
	 * @param int $customer_id Customer id.
	 * @return void
	 */
	private static function attach_customer_to_appointment( $appointment_id, $customer_id ) {
		if ( $appointment_id < 1 || $customer_id < 1 ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$wpdb->update(
			$table_name,
			array( 'customer_id' => $customer_id ),
			array( 'id' => $appointment_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Send free booking verification email.
	 *
	 * @param array  $appointment_ids Appointment ids.
	 * @param string $email Email.
	 * @param string $first_name First name.
	 * @return bool
	 */
	private static function send_free_booking_verification_email( $appointment_ids, $email, $first_name ) {
		if ( ! is_array( $appointment_ids ) || empty( $appointment_ids ) ) {
			return false;
		}

		$appointment_ids = array_values( array_filter( array_map( 'absint', $appointment_ids ) ) );

		if ( empty( $appointment_ids ) ) {
			return false;
		}

		$token = wp_generate_password( 32, false, false );

		set_transient(
			'zab_free_verify_' . $token,
			array(
				'appointment_ids' => $appointment_ids,
				'email'          => $email,
			),
			HOUR_IN_SECONDS
		);

		$link = add_query_arg(
			array(
				'zab_verify_booking' => rawurlencode( $token ),
			),
			home_url( '/' )
		);

		$subject = __( 'Confirm your booking email', 'zeka-appointment-booking' );
		$count   = count( $appointment_ids );
		$message = sprintf(
			/* translators: %1$s: first name, %2$s: appointment count, %3$s: verification link */
			__( "Hi %1\$s,\n\nPlease confirm your %2\$d free booking(s) by clicking this link:\n%3\$s\n\nThis link expires in 1 hour.", 'zeka-appointment-booking' ),
			$first_name,
			$count,
			$link
		);

		return wp_mail( $email, $subject, $message );
	}

	/**
	 * Handle verification link callback for free bookings.
	 *
	 * @return void
	 */
	public static function handle_free_booking_verification() {
		self::release_expired_pending_locks();

		$token = isset( $_GET['zab_verify_booking'] ) ? sanitize_text_field( wp_unslash( $_GET['zab_verify_booking'] ) ) : '';

		if ( '' === $token ) {
			return;
		}

		$payload = get_transient( 'zab_free_verify_' . $token );
		if ( ! is_array( $payload ) || empty( $payload['appointment_ids'] ) || ! is_array( $payload['appointment_ids'] ) ) {
			$redirect = self::get_confirmation_redirect_url(
				array(
					'zab_booking' => 'verify_failed',
				)
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		$appointment_ids = array_values( array_filter( array_map( 'absint', $payload['appointment_ids'] ) ) );
		delete_transient( 'zab_free_verify_' . $token );

		if ( empty( $appointment_ids ) ) {
			$redirect = self::get_confirmation_redirect_url(
				array(
					'zab_booking' => 'verify_failed',
				)
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		foreach ( $appointment_ids as $appointment_id ) {
			self::set_appointment_status( $appointment_id, 'confirmed', 0 );
			do_action( 'zab_appointment_confirmed', $appointment_id );
		}

		$primary_id = (int) $appointment_ids[0];

		$redirect = self::get_confirmation_redirect_url(
			array(
				'zab_booking'    => 'confirmed',
				'appointment_id' => $primary_id,
				'appointment_count' => count( $appointment_ids ),
			)
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Build confirmation/thank-you redirect URL.
	 *
	 * @param array $query_args Query arguments.
	 * @return string
	 */
	private static function get_confirmation_redirect_url( $query_args ) {
		$base_url = home_url( '/' );
		$page_id  = self::get_or_create_confirmation_page_id();

		if ( $page_id > 0 ) {
			$permalink = get_permalink( $page_id );
			if ( is_string( $permalink ) && '' !== $permalink ) {
				$base_url = $permalink;
			}
		}

		return add_query_arg( $query_args, $base_url );
	}

	/**
	 * Ensure a booking confirmation page exists and return its ID.
	 *
	 * @return int
	 */
	private static function get_or_create_confirmation_page_id() {
		$page_id = absint( get_option( self::CONFIRMATION_PAGE_OPTION_KEY, 0 ) );

		if ( $page_id > 0 && 'page' === get_post_type( $page_id ) && 'trash' !== get_post_status( $page_id ) ) {
			return $page_id;
		}

		$existing = get_page_by_path( 'booking-confirmation', OBJECT, 'page' );
		if ( $existing instanceof WP_Post ) {
			update_option( self::CONFIRMATION_PAGE_OPTION_KEY, (int) $existing->ID );
			return (int) $existing->ID;
		}

		$new_page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Booking Confirmation', 'zeka-appointment-booking' ),
				'post_name'    => 'booking-confirmation',
				'post_content' => '[zab_booking_confirmation]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( is_wp_error( $new_page_id ) || absint( $new_page_id ) < 1 ) {
			return 0;
		}

		update_option( self::CONFIRMATION_PAGE_OPTION_KEY, absint( $new_page_id ) );

		return absint( $new_page_id );
	}

	/**
	 * Check if cart contains booking appointment item.
	 *
	 * @return bool
	 */
	private static function cart_has_booking_item() {
		if ( ! function_exists( 'WC' ) || null === WC()->cart ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( ! empty( $item['zab_appointment_id'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether a cart line item is a booking appointment item.
	 *
	 * @param array $cart_item Cart item data.
	 * @return bool
	 */
	private static function is_booking_cart_item( $cart_item ) {
		return is_array( $cart_item ) && ! empty( $cart_item['zab_appointment_id'] );
	}

	/**
	 * Cancel pending appointment when its cart item is removed.
	 *
	 * @param string  $cart_item_key Cart item key.
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public static function handle_booking_cart_item_removed( $cart_item_key, $cart ) {
		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		$cart_item = $cart->get_cart_item( $cart_item_key );
		if ( ! self::is_booking_cart_item( $cart_item ) ) {
			return;
		}

		$appointment_id = absint( $cart_item['zab_appointment_id'] );
		if ( $appointment_id < 1 ) {
			return;
		}

		self::cancel_pending_appointment_from_cart( $appointment_id );
	}

	/**
	 * Cancel pending booking appointments when the whole cart is emptied.
	 *
	 * @param bool $clear_persistent_cart Whether persistent cart will be cleared.
	 * @return void
	 */
	public static function handle_booking_cart_emptied( $clear_persistent_cart ) {
		unset( $clear_persistent_cart );

		if ( ! function_exists( 'WC' ) || null === WC()->cart ) {
			return;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! self::is_booking_cart_item( $cart_item ) ) {
				continue;
			}

			$appointment_id = absint( $cart_item['zab_appointment_id'] );
			if ( $appointment_id < 1 ) {
				continue;
			}

			self::cancel_pending_appointment_from_cart( $appointment_id );
		}
	}

	/**
	 * Restore pending appointment when a removed cart item is restored.
	 *
	 * @param string  $cart_item_key Cart item key.
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public static function handle_booking_cart_item_restored( $cart_item_key, $cart ) {
		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		$cart_item = $cart->get_cart_item( $cart_item_key );
		if ( ! self::is_booking_cart_item( $cart_item ) ) {
			return;
		}

		$appointment_id = absint( $cart_item['zab_appointment_id'] );
		if ( $appointment_id < 1 ) {
			return;
		}

		self::restore_pending_appointment_to_cart( $appointment_id );
	}

	/**
	 * Cancel a cart-held pending appointment row.
	 *
	 * @param int $appointment_id Appointment id.
	 * @return void
	 */
	private static function cancel_pending_appointment_from_cart( $appointment_id ) {
		$appointment_id = absint( $appointment_id );

		if ( $appointment_id < 1 ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'booking_appointments';
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table_name} SET status = 'cancelled', lock_expires_at = NULL WHERE id = %d AND status = 'pending' AND ( order_id IS NULL OR order_id = 0 )",
				$appointment_id
			)
		);
	}

	/**
	 * Restore a cart-held pending appointment row after undo.
	 *
	 * @param int $appointment_id Appointment id.
	 * @return void
	 */
	private static function restore_pending_appointment_to_cart( $appointment_id ) {
		$appointment_id = absint( $appointment_id );

		if ( $appointment_id < 1 ) {
			return;
		}

		global $wpdb;

		$table_name      = $wpdb->prefix . 'booking_appointments';
		$lock_expires_at = self::get_lock_expiry_utc();

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table_name} SET status = 'pending', lock_expires_at = %s WHERE id = %d AND status = 'cancelled' AND ( order_id IS NULL OR order_id = 0 )",
				$lock_expires_at,
				$appointment_id
			)
		);
	}

	/**
	 * Render fixed quantity for booking cart items.
	 *
	 * @param string $product_quantity Existing quantity HTML.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $cart_item Cart item data.
	 * @return string
	 */
	public static function render_booking_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( ! self::is_booking_cart_item( $cart_item ) ) {
			return $product_quantity;
		}

		return '1<input type="hidden" name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="1" />';
	}

	/**
	 * Block manual quantity updates for booking cart items.
	 *
	 * @param bool   $passed Validation state.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $cart_item Cart item data.
	 * @param int    $quantity Requested quantity.
	 * @return bool
	 */
	public static function prevent_booking_cart_quantity_update( $passed, $cart_item_key, $cart_item, $quantity ) {
		if ( ! self::is_booking_cart_item( $cart_item ) ) {
			return $passed;
		}

		if ( 1 !== (int) $quantity ) {
			wc_add_notice( __( 'Appointment services stay at quantity 1. Add more appointments as separate selections.', 'zeka-appointment-booking' ), 'notice' );
			return false;
		}

		return $passed;
	}

	/**
	 * Force booking product to be sold individually.
	 *
	 * @param bool       $sold_individually Current sold individually flag.
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	public static function force_booking_product_sold_individually( $sold_individually, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $sold_individually;
		}

		$booking_product_id = (int) get_option( self::PRODUCT_OPTION_KEY, 0 );

		if ( $booking_product_id > 0 && $product->get_id() === $booking_product_id ) {
			return true;
		}

		return $sold_individually;
	}

	/**
	 * Normalize booking cart items to quantity 1 after cart updates.
	 *
	 * @param string  $cart_item_key Cart item key.
	 * @param int     $quantity New quantity.
	 * @param int     $old_quantity Old quantity.
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public static function normalize_booking_cart_item_quantity( $cart_item_key, $quantity, $old_quantity, $cart ) {
		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		$cart_item = $cart->get_cart_item( $cart_item_key );
		if ( ! self::is_booking_cart_item( $cart_item ) ) {
			return;
		}

		if ( 1 !== (int) $quantity ) {
			$cart->set_quantity( $cart_item_key, 1, false );
		}
	}

	/**
	 * Hide internal booking IDs from customer-visible order item meta.
	 *
	 * @param array $hidden_meta Existing hidden item meta keys.
	 * @return array
	 */
	public static function hide_booking_order_item_meta( $hidden_meta ) {
		$hidden_meta   = is_array( $hidden_meta ) ? $hidden_meta : array();
		$hidden_meta[] = 'appointment_id';
		$hidden_meta[] = 'service_id';

		return array_values( array_unique( $hidden_meta ) );
	}

	/**
	 * Prevent mixing booking items with regular WooCommerce products.
	 *
	 * @param bool   $passed Validation status.
	 * @param int    $product_id Product ID being added.
	 * @param int    $quantity Quantity.
	 * @param int    $variation_id Variation ID.
	 * @param array  $variations Variations.
	 * @return bool
	 */
	public static function prevent_product_booking_mix( $passed, $product_id, $quantity, $variation_id = null, $variations = array() ) {
		if ( ! self::is_woocommerce_active() ) {
			return $passed;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $passed;
		}

		$is_booking_product = $product_id === (int) get_option( self::PRODUCT_OPTION_KEY, 0 );
		$cart_has_booking   = self::cart_has_booking_item();
		$cart_has_products  = self::cart_has_non_booking_items();

		// If trying to add booking product but cart has regular products, prevent it.
		if ( $is_booking_product && $cart_has_products ) {
			wc_add_notice(
				__( 'Booking items cannot be mixed with regular products. Please clear your cart first or complete checkout.', 'zeka-appointment-booking' ),
				'error'
			);
			return false;
		}

		// If trying to add regular product but cart has booking items, clear cart and show notice.
		if ( ! $is_booking_product && $cart_has_booking ) {
			if ( null !== WC()->cart ) {
				WC()->cart->empty_cart();
			}
			wc_add_notice(
				__( 'Booking items were removed from cart. You cannot mix bookings with regular products.', 'zeka-appointment-booking' ),
				'info'
			);
		}

		return $passed;
	}

	/**
	 * Check if cart contains non-booking items.
	 *
	 * @return bool
	 */
	private static function cart_has_non_booking_items() {
		if ( ! self::is_woocommerce_active() || null === WC()->cart ) {
			return false;
		}

		$booking_product_id = (int) get_option( self::PRODUCT_OPTION_KEY, 0 );

		foreach ( WC()->cart->get_cart() as $item ) {
			$product = isset( $item['data'] ) ? $item['data'] : null;
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$product_id = $product->get_id();
			if ( $product_id !== $booking_product_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether WooCommerce is available.
	 *
	 * @return bool
	 */
	private static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
	}
}
