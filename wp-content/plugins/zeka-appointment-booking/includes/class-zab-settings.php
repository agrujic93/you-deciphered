<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZAB_Settings {

	/**
	 * Option key used for plugin settings.
	 */
	const OPTION_KEY = 'zab_settings';

	/**
	 * Register hooks for the admin settings screen.
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_zab_add_service', array( __CLASS__, 'handle_add_service' ) );
		add_action( 'admin_post_zab_update_service', array( __CLASS__, 'handle_update_service' ) );
		add_action( 'admin_post_zab_delete_service', array( __CLASS__, 'handle_delete_service' ) );
		add_action( 'admin_post_zab_add_exception', array( __CLASS__, 'handle_add_exception' ) );
		add_action( 'admin_post_zab_delete_exception', array( __CLASS__, 'handle_delete_exception' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue global admin CSS.
	 *
	 * @param string $hook_suffix The current admin page.
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		$valid_pages = array(
			'zeka-appointment-booking-settings',
			'zab-calendar',
			'zab-reports',
			'zab-debug'
		);

		$is_plugin_page = false;
		foreach ( $valid_pages as $page_slug ) {
			if ( strpos( $hook_suffix, $page_slug ) !== false ) {
				$is_plugin_page = true;
				break;
			}
		}

		if ( ! $is_plugin_page ) {
			return;
		}

		$style_rel_path = 'assets/css/zab-admin.css';
		$style_abs_path = ZAB_PLUGIN_DIR . $style_rel_path;
		$style_version  = file_exists( $style_abs_path ) ? (string) filemtime( $style_abs_path ) : ZAB_PLUGIN_VERSION;

		wp_enqueue_style(
			'zab-admin-style',
			plugins_url( $style_rel_path, ZAB_PLUGIN_FILE ),
			array(),
			$style_version
		);
	}

	/**
	 * Add defaults on first activation.
	 *
	 * @return void
	 */
	public static function maybe_add_default_settings() {
		if ( false === get_option( self::OPTION_KEY, false ) ) {
			add_option( self::OPTION_KEY, self::get_defaults() );
		}
	}

	/**
	 * Register the plugin settings page.
	 *
	 * @return void
	 */
	public static function register_admin_page() {
		add_options_page(
			__( 'Booking Settings', 'zeka-appointment-booking' ),
			__( 'Booking Settings', 'zeka-appointment-booking' ),
			'manage_options',
			'zeka-appointment-booking-settings',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			'zab_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'zab_general_section',
			__( 'Booking Configuration', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_section_description' ),
			'zeka-appointment-booking-settings'
		);

		add_settings_field(
			'slot_duration',
			__( 'Slot Duration (minutes)', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_slot_duration_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'enable_buffer',
			__( 'Enable Buffer Time', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_enable_buffer_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'buffer_minutes',
			__( 'Buffer Time (minutes)', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_buffer_minutes_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'minimum_notice_minutes',
			__( 'Minimum Notice (minutes)', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_minimum_notice_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'timezone_string',
			__( 'Booking Timezone', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_timezone_string_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'redirect_to_checkout',
			__( 'Redirect to Checkout', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_redirect_to_checkout_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'enable_multiple_appointments',
			__( 'Multiple Appointments', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_enable_multiple_appointments_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);

		add_settings_field(
			'working_hours',
			__( 'Working Hours', 'zeka-appointment-booking' ),
			array( __CLASS__, 'render_working_hours_field' ),
			'zeka-appointment-booking-settings',
			'zab_general_section'
		);
	}

	/**
	 * Sanitize incoming settings before saving.
	 *
	 * @param array $input Raw settings data.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$defaults = self::get_defaults();
		$input    = is_array( $input ) ? $input : array();

		$sanitized = array(
			'slot_duration' => isset( $input['slot_duration'] ) ? absint( $input['slot_duration'] ) : $defaults['slot_duration'],
			'enable_buffer' => ! empty( $input['enable_buffer'] ) ? 1 : 0,
			'buffer_minutes' => isset( $input['buffer_minutes'] ) ? absint( $input['buffer_minutes'] ) : $defaults['buffer_minutes'],
			'minimum_notice_minutes' => isset( $input['minimum_notice_minutes'] ) ? absint( $input['minimum_notice_minutes'] ) : $defaults['minimum_notice_minutes'],
			'timezone_string' => isset( $input['timezone_string'] ) ? self::sanitize_timezone_string( $input['timezone_string'], $defaults['timezone_string'] ) : $defaults['timezone_string'],
			'redirect_to_checkout' => ! empty( $input['redirect_to_checkout'] ) ? 1 : 0,
			'free_service_behavior' => 'checkout',
			'enable_multiple_appointments' => ! empty( $input['enable_multiple_appointments'] ) ? 1 : 0,
			'working_hours' => $defaults['working_hours'],
		);

		if ( $sanitized['slot_duration'] < 5 ) {
			$sanitized['slot_duration'] = $defaults['slot_duration'];
		}

		if ( $sanitized['buffer_minutes'] > 120 ) {
			$sanitized['buffer_minutes'] = 120;
		}

		if ( $sanitized['minimum_notice_minutes'] > 1440 ) {
			$sanitized['minimum_notice_minutes'] = 1440;
		}

		if ( isset( $input['working_hours'] ) && is_array( $input['working_hours'] ) ) {
			foreach ( $defaults['working_hours'] as $day => $day_defaults ) {
				$day_input = isset( $input['working_hours'][ $day ] ) && is_array( $input['working_hours'][ $day ] )
					? $input['working_hours'][ $day ]
					: array();

				$is_closed = ! empty( $day_input['closed'] ) ? 1 : 0;
				$start     = isset( $day_input['start'] ) ? self::sanitize_time_value( $day_input['start'] ) : $day_defaults['start'];
				$end       = isset( $day_input['end'] ) ? self::sanitize_time_value( $day_input['end'] ) : $day_defaults['end'];

				if ( empty( $start ) ) {
					$start = $day_defaults['start'];
				}

				if ( empty( $end ) ) {
					$end = $day_defaults['end'];
				}

				$sanitized['working_hours'][ $day ] = array(
					'start'  => $start,
					'end'    => $end,
					'closed' => $is_closed,
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Render settings section intro.
	 *
	 * @return void
	 */
	public static function render_section_description() {
		echo '<p>' . esc_html__( 'Configure booking timezone, slot timing, booking notice cutoff, and weekly working hours. Visitors see slot times in their own local timezone automatically.', 'zeka-appointment-booking' ) . '</p>';
	}

	/**
	 * Render booking timezone field.
	 *
	 * @return void
	 */
	public static function render_timezone_string_field() {
		$settings = self::get_settings();
		?>
		<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[timezone_string]">
			<?php echo wp_timezone_choice( $settings['timezone_string'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</select>
		<p class="description" style="margin: 6px 0 0;">
			<?php esc_html_e( 'Business hours, availability rules, and stored booking times use this timezone. Frontend visitors still see slots converted to their own timezone.', 'zeka-appointment-booking' ); ?>
		</p>
		<?php
	}

	/**
	 * Render slot duration field.
	 *
	 * @return void
	 */
	public static function render_slot_duration_field() {
		$settings = self::get_settings();
		?>
		<input
			type="number"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[slot_duration]"
			value="<?php echo esc_attr( $settings['slot_duration'] ); ?>"
			min="5"
			step="5"
			class="small-text"
		/>
		<?php
	}

	/**
	 * Render enable buffer checkbox.
	 *
	 * @return void
	 */
	public static function render_enable_buffer_field() {
		$settings = self::get_settings();
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enable_buffer]"
				value="1"
				<?php checked( 1, (int) $settings['enable_buffer'] ); ?>
			/>
			<?php esc_html_e( 'Add buffer time between appointments', 'zeka-appointment-booking' ); ?>
		</label>
		<?php
	}

	/**
	 * Render buffer time field.
	 *
	 * @return void
	 */
	public static function render_buffer_minutes_field() {
		$settings = self::get_settings();
		?>
		<input
			type="number"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[buffer_minutes]"
			value="<?php echo esc_attr( $settings['buffer_minutes'] ); ?>"
			min="0"
			max="120"
			step="5"
			class="small-text"
		/>
		<?php
	}

	/**
	 * Render minimum notice field.
	 *
	 * @return void
	 */
	public static function render_minimum_notice_field() {
		$settings = self::get_settings();
		?>
		<input
			type="number"
			name="<?php echo esc_attr( self::OPTION_KEY ); ?>[minimum_notice_minutes]"
			value="<?php echo esc_attr( $settings['minimum_notice_minutes'] ); ?>"
			min="0"
			max="1440"
			step="5"
			class="small-text"
		/>
		<p class="description" style="margin: 6px 0 0;">
			<?php esc_html_e( 'Users can only book slots that start at least this many minutes from now (example: 30 blocks 9:00 slot after 8:30).', 'zeka-appointment-booking' ); ?>
		</p>
		<?php
	}

	/**
	 * Render redirect-to-checkout checkbox field.
	 *
	 * @return void
	 */
	public static function render_redirect_to_checkout_field() {
		$settings = self::get_settings();
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_KEY ); ?>[redirect_to_checkout]"
				value="1"
				<?php checked( 1, (int) $settings['redirect_to_checkout'] ); ?>
			/>
			<?php esc_html_e( 'After selecting a slot, continue directly to checkout.', 'zeka-appointment-booking' ); ?>
		</label>
		<?php
	}

	/**
	 * Render free service behavior field.
	 *
	 * @return void
	 */
	public static function render_free_service_behavior_field() {
		$settings = self::get_settings();
		$value    = isset( $settings['free_service_behavior'] ) ? $settings['free_service_behavior'] : 'checkout';
		?>
		<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[free_service_behavior]">
			<option value="checkout" <?php selected( 'checkout', $value ); ?>><?php esc_html_e( 'Use WooCommerce checkout (default)', 'zeka-appointment-booking' ); ?></option>
			<option value="instant_confirm" <?php selected( 'instant_confirm', $value ); ?>><?php esc_html_e( 'Instant confirm (skip checkout for free services)', 'zeka-appointment-booking' ); ?></option>
			<option value="verify_email" <?php selected( 'verify_email', $value ); ?>><?php esc_html_e( 'Verify email first (skip checkout for free services)', 'zeka-appointment-booking' ); ?></option>
		</select>
		<p class="description" style="margin: 6px 0 0;">
			<?php esc_html_e( 'Applies only when service price is 0.00.', 'zeka-appointment-booking' ); ?>
		</p>
		<?php
	}

	/**
	 * Render multiple appointments toggle.
	 *
	 * @return void
	 */
	public static function render_enable_multiple_appointments_field() {
		$settings = self::get_settings();
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enable_multiple_appointments]"
				value="1"
				<?php checked( 1, (int) $settings['enable_multiple_appointments'] ); ?>
			/>
			<?php esc_html_e( 'Allow users to select multiple slots in one checkout', 'zeka-appointment-booking' ); ?>
		</label>
		<?php
	}

	/**
	 * Render working hours matrix.
	 *
	 * @return void
	 */
	public static function render_working_hours_field() {
		$settings = self::get_settings();
		$labels   = self::day_labels();
		?>
		<table class="widefat striped" style="max-width: 700px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Day', 'zeka-appointment-booking' ); ?></th>
					<th><?php esc_html_e( 'Start', 'zeka-appointment-booking' ); ?></th>
					<th><?php esc_html_e( 'End', 'zeka-appointment-booking' ); ?></th>
					<th><?php esc_html_e( 'Closed', 'zeka-appointment-booking' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $labels as $day_key => $day_label ) : ?>
					<tr>
						<td><?php echo esc_html( $day_label ); ?></td>
						<td>
							<input
								type="time"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>[working_hours][<?php echo esc_attr( $day_key ); ?>][start]"
								value="<?php echo esc_attr( $settings['working_hours'][ $day_key ]['start'] ); ?>"
							/>
						</td>
						<td>
							<input
								type="time"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>[working_hours][<?php echo esc_attr( $day_key ); ?>][end]"
								value="<?php echo esc_attr( $settings['working_hours'][ $day_key ]['end'] ); ?>"
							/>
						</td>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( self::OPTION_KEY ); ?>[working_hours][<?php echo esc_attr( $day_key ); ?>][closed]"
									value="1"
									<?php checked( 1, (int) $settings['working_hours'][ $day_key ]['closed'] ); ?>
								/>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render plugin settings screen.
	 *
	 * @return void
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$exceptions = self::get_exceptions();
		$services   = self::get_services();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Appointment Booking Settings', 'zeka-appointment-booking' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'zab_settings_group' );
				do_settings_sections( 'zeka-appointment-booking-settings' );
				submit_button();
				?>
			</form>

			<hr />
			<h2><?php esc_html_e( 'Services', 'zeka-appointment-booking' ); ?></h2>
			<p><?php esc_html_e( 'Create at least one service so customers can complete booking checkout.', 'zeka-appointment-booking' ); ?></p>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="zab-inline-form zab-service-form">
				<input type="hidden" name="action" value="zab_add_service" />
				<?php wp_nonce_field( 'zab_add_service' ); ?>
				
				<div class="zab-form-row">
					<div class="zab-form-col">
						<label for="zab_service_name"><?php esc_html_e( 'Name', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_service_name" type="text" name="name" maxlength="191" required />
					</div>
					
					<div class="zab-form-col">
						<label for="zab_service_duration"><?php esc_html_e( 'Duration (min)', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_service_duration" type="number" name="duration_minutes" min="5" step="5" value="60" required />
					</div>
					
					<div class="zab-form-col">
						<label for="zab_service_price"><?php esc_html_e( 'Price', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_service_price" type="number" name="price" min="0" step="0.01" value="0.00" required />
					</div>
				</div>

				<div class="zab-form-row col-full">
					<div class="zab-form-col">
						<label for="zab_service_description"><?php esc_html_e( 'Description', 'zeka-appointment-booking' ); ?></label>
						<textarea id="zab_service_description" name="description" rows="2" maxlength="500"></textarea>
					</div>
				</div>

				<div class="zab-form-actions">
					<?php submit_button( __( 'Add Service', 'zeka-appointment-booking' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<div class="zab-services-list">
				<div class="zab-list-header">
					<div class="zcol">Name</div>
					<div class="zcol">Duration</div>
					<div class="zcol">Price</div>
					<div class="zcol">Description</div>
					<div class="zcol zcol-actions">Actions</div>
				</div>
				<div class="zab-list-body">
					<?php if ( empty( $services ) ) : ?>
						<div class="zab-list-empty"><?php esc_html_e( 'No services yet. Add your first service above.', 'zeka-appointment-booking' ); ?></div>
					<?php else : ?>
						<?php foreach ( $services as $service ) : ?>
							<div class="zab-list-row">
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="zab-row-form">
									<input type="hidden" name="action" value="zab_update_service" />
									<input type="hidden" name="service_id" value="<?php echo esc_attr( $service['id'] ); ?>" />
									<?php wp_nonce_field( 'zab_update_service_' . $service['id'] ); ?>
									
									<div class="zcol">
										<input type="text" name="name" value="<?php echo esc_attr( $service['name'] ); ?>" maxlength="191" required />
									</div>
									<div class="zcol zab-input-group">
										<input type="number" name="duration_minutes" value="<?php echo esc_attr( (int) $service['duration_minutes'] ); ?>" min="5" step="5" required />
										<span>min</span>
									</div>
									<div class="zcol">
										<input type="number" name="price" value="<?php echo esc_attr( number_format( (float) $service['price'], 2, '.', '' ) ); ?>" min="0" step="0.01" required />
									</div>
									<div class="zcol">
										<input type="text" name="description" value="<?php echo esc_attr( $service['description'] ); ?>" maxlength="500" />
									</div>
									<div class="zcol zcol-actions">
										<?php submit_button( __( 'Save', 'zeka-appointment-booking' ), 'secondary', 'submit', false ); ?>
									</div>
								</form>
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="zab-row-delete-form">
									<input type="hidden" name="action" value="zab_delete_service" />
									<input type="hidden" name="service_id" value="<?php echo esc_attr( $service['id'] ); ?>" />
									<?php wp_nonce_field( 'zab_delete_service_' . $service['id'] ); ?>
									<?php submit_button( __( 'Delete', 'zeka-appointment-booking' ), 'delete', 'submit', false ); ?>
								</form>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<hr />
			<h2><?php esc_html_e( 'Specific Closed Dates', 'zeka-appointment-booking' ); ?></h2>
			<p><?php esc_html_e( 'Add one-off closed dates (holidays or time off) that override the weekly schedule.', 'zeka-appointment-booking' ); ?></p>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="zab-inline-form zab-exception-form">
				<input type="hidden" name="action" value="zab_add_exception" />
				<?php wp_nonce_field( 'zab_add_exception' ); ?>
				
				<div class="zab-form-row">
					<div class="zab-form-col">
						<label for="zab_exception_date"><?php esc_html_e( 'Date', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_exception_date" type="date" name="exception_date" required />
					</div>
					
					<div class="zab-form-col">
						<label for="zab_exception_start_time"><?php esc_html_e( 'Start Time', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_exception_start_time" type="time" name="start_time" />
					</div>
					
					<div class="zab-form-col">
						<label for="zab_exception_end_time"><?php esc_html_e( 'End Time', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_exception_end_time" type="time" name="end_time" />
					</div>
					
					<div class="zab-form-col">
						<label for="zab_exception_reason"><?php esc_html_e( 'Reason', 'zeka-appointment-booking' ); ?></label>
						<input id="zab_exception_reason" type="text" name="reason" maxlength="255" />
					</div>
				</div>
				
				<div class="zab-form-actions">
					<?php submit_button( __( 'Add Closed Date', 'zeka-appointment-booking' ), 'primary', 'submit', false ); ?>
				</div>
			</form>
			<p class="description" style="margin-top: -16px; margin-bottom: 24px;">
				<?php esc_html_e( 'Leave start and end empty for a full-day closure. Fill both to close a specific time range on that date.', 'zeka-appointment-booking' ); ?>
			</p>

			<div class="zab-services-list">
				<div class="zab-list-header">
					<div class="zcol">Date</div>
					<div class="zcol">Time Window</div>
					<div class="zcol">Reason</div>
					<div class="zcol zcol-actions-single">Action</div>
				</div>
				<div class="zab-list-body">
					<?php if ( empty( $exceptions ) ) : ?>
						<div class="zab-list-empty"><?php esc_html_e( 'No specific closed dates yet.', 'zeka-appointment-booking' ); ?></div>
					<?php else : ?>
						<?php foreach ( $exceptions as $exception ) : ?>
							<?php
							$time_window = __( 'Full day', 'zeka-appointment-booking' );
							if ( ! empty( $exception['start_time'] ) && ! empty( $exception['end_time'] ) ) {
								$time_window = $exception['start_time'] . ' - ' . $exception['end_time'];
							}
							?>
							<div class="zab-list-row">
								<div class="zcol text-col"><?php echo esc_html( $exception['exception_date'] ); ?></div>
								<div class="zcol text-col"><?php echo esc_html( $time_window ); ?></div>
								<div class="zcol text-col"><?php echo esc_html( $exception['reason'] ); ?></div>
								<div class="zcol zcol-actions-single flex-end">
									<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="zab-row-delete-form" style="margin: 0;">
										<input type="hidden" name="action" value="zab_delete_exception" />
										<input type="hidden" name="exception_id" value="<?php echo esc_attr( $exception['id'] ); ?>" />
										<?php wp_nonce_field( 'zab_delete_exception_' . $exception['id'] ); ?>
										<?php submit_button( __( 'Delete', 'zeka-appointment-booking' ), 'delete', 'submit', false ); ?>
									</form>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle one-off closed date creation.
	 *
	 * @return void
	 */
	public static function handle_add_exception() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'zeka-appointment-booking' ) );
		}

		check_admin_referer( 'zab_add_exception' );

		$exception_date = isset( $_POST['exception_date'] ) ? sanitize_text_field( wp_unslash( $_POST['exception_date'] ) ) : '';
		$start_time     = isset( $_POST['start_time'] ) ? self::sanitize_time_value( wp_unslash( $_POST['start_time'] ) ) : '';
		$end_time       = isset( $_POST['end_time'] ) ? self::sanitize_time_value( wp_unslash( $_POST['end_time'] ) ) : '';
		$reason         = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( ! self::is_valid_date( $exception_date ) ) {
			self::redirect_with_notice( 'invalid_date' );
		}

		$has_start = ! empty( $start_time );
		$has_end   = ! empty( $end_time );

		if ( $has_start xor $has_end ) {
			self::redirect_with_notice( 'invalid_time' );
		}

		if ( $has_start && $has_end && ! self::is_valid_time_range( $start_time, $end_time ) ) {
			self::redirect_with_notice( 'invalid_time' );
		}

		global $wpdb;
		$table_name = self::get_exceptions_table_name();

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'exception_date' => $exception_date,
				'start_time'     => $has_start ? $start_time : null,
				'end_time'       => $has_end ? $end_time : null,
				'reason'         => $reason,
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			if ( false !== strpos( strtolower( $wpdb->last_error ), 'duplicate' ) ) {
				self::redirect_with_notice( 'duplicate_date' );
			}

			self::redirect_with_notice( 'save_failed' );
		}

		self::redirect_with_notice( 'date_added' );
	}

	/**
	 * Handle service creation.
	 *
	 * @return void
	 */
	public static function handle_add_service() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'zeka-appointment-booking' ) );
		}

		check_admin_referer( 'zab_add_service' );

		$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description      = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$duration_minutes = isset( $_POST['duration_minutes'] ) ? absint( wp_unslash( $_POST['duration_minutes'] ) ) : 0;
		$price            = isset( $_POST['price'] ) ? (float) wp_unslash( $_POST['price'] ) : 0;

		if ( '' === $name || $duration_minutes < 5 ) {
			self::redirect_with_notice( 'service_invalid' );
		}

		if ( $price < 0 ) {
			$price = 0;
		}

		global $wpdb;
		$table_name = self::get_services_table_name();

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'name'             => $name,
				'description'      => $description,
				'duration_minutes' => $duration_minutes,
				'price'            => number_format( $price, 2, '.', '' ),
			),
			array( '%s', '%s', '%d', '%f' )
		);

		if ( false === $inserted ) {
			self::redirect_with_notice( 'service_save_failed' );
		}

		self::redirect_with_notice( 'service_added' );
	}

	/**
	 * Handle service update.
	 *
	 * @return void
	 */
	public static function handle_update_service() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'zeka-appointment-booking' ) );
		}

		$service_id = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;

		if ( $service_id < 1 ) {
			self::redirect_with_notice( 'service_update_failed' );
		}

		check_admin_referer( 'zab_update_service_' . $service_id );

		$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description      = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$duration_minutes = isset( $_POST['duration_minutes'] ) ? absint( wp_unslash( $_POST['duration_minutes'] ) ) : 0;
		$price_raw        = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '0';
		$price            = (float) str_replace( ',', '.', $price_raw );

		if ( '' === $name || $duration_minutes < 5 ) {
			self::redirect_with_notice( 'service_invalid' );
		}

		if ( $price < 0 ) {
			$price = 0;
		}

		global $wpdb;
		$table_name = self::get_services_table_name();

		$updated = $wpdb->update(
			$table_name,
			array(
				'name'             => $name,
				'description'      => $description,
				'duration_minutes' => $duration_minutes,
				'price'            => number_format( $price, 2, '.', '' ),
			),
			array( 'id' => $service_id ),
			array( '%s', '%s', '%d', '%f' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			self::redirect_with_notice( 'service_update_failed' );
		}

		self::redirect_with_notice( 'service_updated' );
	}

	/**
	 * Handle service deletion.
	 *
	 * @return void
	 */
	public static function handle_delete_service() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'zeka-appointment-booking' ) );
		}

		$service_id = isset( $_POST['service_id'] ) ? absint( wp_unslash( $_POST['service_id'] ) ) : 0;

		if ( $service_id < 1 ) {
			self::redirect_with_notice( 'service_delete_failed' );
		}

		check_admin_referer( 'zab_delete_service_' . $service_id );

		global $wpdb;

		$appointments_table = $wpdb->prefix . 'booking_appointments';
		$in_use             = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM {$appointments_table} WHERE service_id = %d",
				$service_id
			)
		);

		if ( $in_use > 0 ) {
			self::redirect_with_notice( 'service_in_use' );
		}

		$table_name = self::get_services_table_name();
		$deleted    = $wpdb->delete(
			$table_name,
			array( 'id' => $service_id ),
			array( '%d' )
		);

		if ( false === $deleted ) {
			self::redirect_with_notice( 'service_delete_failed' );
		}

		self::redirect_with_notice( 'service_deleted' );
	}

	/**
	 * Handle closed date deletion.
	 *
	 * @return void
	 */
	public static function handle_delete_exception() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'zeka-appointment-booking' ) );
		}

		$exception_id = isset( $_POST['exception_id'] ) ? absint( wp_unslash( $_POST['exception_id'] ) ) : 0;

		if ( $exception_id < 1 ) {
			self::redirect_with_notice( 'delete_failed' );
		}

		check_admin_referer( 'zab_delete_exception_' . $exception_id );

		global $wpdb;
		$table_name = self::get_exceptions_table_name();

		$deleted = $wpdb->delete(
			$table_name,
			array( 'id' => $exception_id ),
			array( '%d' )
		);

		if ( false === $deleted ) {
			self::redirect_with_notice( 'delete_failed' );
		}

		self::redirect_with_notice( 'date_deleted' );
	}

	/**
	 * Render admin notices for exception actions.
	 *
	 * @return void
	 */
	public static function render_admin_notices() {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( ! is_admin() || 'zeka-appointment-booking-settings' !== $page ) {
			return;
		}

		$notice = isset( $_GET['zab_notice'] ) ? sanitize_key( wp_unslash( $_GET['zab_notice'] ) ) : '';

		if ( empty( $notice ) ) {
			return;
		}

		$messages = array(
			'service_added'       => array( 'type' => 'updated', 'text' => __( 'Service added successfully.', 'zeka-appointment-booking' ) ),
			'service_updated'     => array( 'type' => 'updated', 'text' => __( 'Service updated successfully.', 'zeka-appointment-booking' ) ),
			'service_deleted'     => array( 'type' => 'updated', 'text' => __( 'Service deleted successfully.', 'zeka-appointment-booking' ) ),
			'service_invalid'     => array( 'type' => 'error', 'text' => __( 'Please provide a valid service name and duration.', 'zeka-appointment-booking' ) ),
			'service_save_failed' => array( 'type' => 'error', 'text' => __( 'Unable to save the service. Please try again.', 'zeka-appointment-booking' ) ),
			'service_update_failed' => array( 'type' => 'error', 'text' => __( 'Unable to update the service. Please try again.', 'zeka-appointment-booking' ) ),
			'service_delete_failed' => array( 'type' => 'error', 'text' => __( 'Unable to delete the service. Please try again.', 'zeka-appointment-booking' ) ),
			'service_in_use'      => array( 'type' => 'error', 'text' => __( 'This service cannot be deleted because it is used by appointments.', 'zeka-appointment-booking' ) ),
			'date_added'     => array( 'type' => 'updated', 'text' => __( 'Closed date added successfully.', 'zeka-appointment-booking' ) ),
			'date_deleted'   => array( 'type' => 'updated', 'text' => __( 'Closed date deleted successfully.', 'zeka-appointment-booking' ) ),
			'duplicate_date' => array( 'type' => 'error', 'text' => __( 'That date is already marked as closed.', 'zeka-appointment-booking' ) ),
			'invalid_date'   => array( 'type' => 'error', 'text' => __( 'Please provide a valid date.', 'zeka-appointment-booking' ) ),
			'invalid_time'   => array( 'type' => 'error', 'text' => __( 'Please provide a valid time range. Use both start and end, and ensure end is after start.', 'zeka-appointment-booking' ) ),
			'save_failed'    => array( 'type' => 'error', 'text' => __( 'Unable to save the closed date. Please try again.', 'zeka-appointment-booking' ) ),
			'delete_failed'  => array( 'type' => 'error', 'text' => __( 'Unable to delete the closed date. Please try again.', 'zeka-appointment-booking' ) ),
		);

		if ( ! isset( $messages[ $notice ] ) ) {
			return;
		}

		$class = 'notice notice-success is-dismissible';

		if ( 'error' === $messages[ $notice ]['type'] ) {
			$class = 'notice notice-error is-dismissible';
		}

		echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $messages[ $notice ]['text'] ) . '</p></div>';
	}

	/**
	 * Return settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = self::get_defaults();
		$stored   = get_option( self::OPTION_KEY, array() );

		$stored = is_array( $stored ) ? $stored : array();

		$settings = wp_parse_args( $stored, $defaults );

		if ( isset( $stored['working_hours'] ) && is_array( $stored['working_hours'] ) ) {
			$settings['working_hours'] = wp_parse_args( $stored['working_hours'], $defaults['working_hours'] );

			foreach ( $defaults['working_hours'] as $day => $day_defaults ) {
				$day_stored = isset( $stored['working_hours'][ $day ] ) && is_array( $stored['working_hours'][ $day ] )
					? $stored['working_hours'][ $day ]
					: array();

				$settings['working_hours'][ $day ] = wp_parse_args( $day_stored, $day_defaults );
			}
		}

		return $settings;
	}

	/**
	 * Return default settings.
	 *
	 * @return array
	 */
	private static function get_defaults() {
		$timezone_string = wp_timezone_string();

		if ( '' === $timezone_string ) {
			$timezone_string = 'UTC';
		}

		return array(
			'slot_duration' => 60,
			'enable_buffer' => 0,
			'buffer_minutes' => 15,
			'minimum_notice_minutes' => 30,
			'timezone_string' => $timezone_string,
			'redirect_to_checkout' => 0,
			'free_service_behavior' => 'checkout',
			'enable_multiple_appointments' => 0,
			'working_hours' => array(
				'monday'    => array( 'start' => '09:00', 'end' => '17:00', 'closed' => 0 ),
				'tuesday'   => array( 'start' => '09:00', 'end' => '17:00', 'closed' => 0 ),
				'wednesday' => array( 'start' => '09:00', 'end' => '17:00', 'closed' => 0 ),
				'thursday'  => array( 'start' => '09:00', 'end' => '17:00', 'closed' => 0 ),
				'friday'    => array( 'start' => '09:00', 'end' => '17:00', 'closed' => 0 ),
				'saturday'  => array( 'start' => '10:00', 'end' => '14:00', 'closed' => 1 ),
				'sunday'    => array( 'start' => '10:00', 'end' => '14:00', 'closed' => 1 ),
			),
		);
	}

	/**
	 * Return day labels.
	 *
	 * @return array
	 */
	private static function day_labels() {
		return array(
			'monday'    => __( 'Monday', 'zeka-appointment-booking' ),
			'tuesday'   => __( 'Tuesday', 'zeka-appointment-booking' ),
			'wednesday' => __( 'Wednesday', 'zeka-appointment-booking' ),
			'thursday'  => __( 'Thursday', 'zeka-appointment-booking' ),
			'friday'    => __( 'Friday', 'zeka-appointment-booking' ),
			'saturday'  => __( 'Saturday', 'zeka-appointment-booking' ),
			'sunday'    => __( 'Sunday', 'zeka-appointment-booking' ),
		);
	}

	/**
	 * Sanitize timezone string.
	 *
	 * @param string $timezone_string Raw timezone string.
	 * @param string $fallback Fallback timezone string.
	 * @return string
	 */
	private static function sanitize_timezone_string( $timezone_string, $fallback ) {
		$timezone_string = sanitize_text_field( $timezone_string );

		if ( '' === $timezone_string ) {
			return $fallback;
		}

		try {
			new DateTimeZone( $timezone_string );
			return $timezone_string;
		} catch ( Exception $exception ) {
			return $fallback;
		}
	}

	/**
	 * Return booking exceptions table name.
	 *
	 * @return string
	 */
	private static function get_exceptions_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'booking_exceptions';
	}

	/**
	 * Return booking services table name.
	 *
	 * @return string
	 */
	private static function get_services_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'booking_services';
	}

	/**
	 * Get all services.
	 *
	 * @return array
	 */
	private static function get_services() {
		global $wpdb;

		$table_name = self::get_services_table_name();
		$query      = "SELECT id, name, description, duration_minutes, price FROM {$table_name} ORDER BY id ASC";
		$results    = $wpdb->get_results( $query, ARRAY_A );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Get all closed date exceptions.
	 *
	 * @return array
	 */
	private static function get_exceptions() {
		global $wpdb;

		$table_name = self::get_exceptions_table_name();
		$query      = "SELECT id, exception_date, start_time, end_time, reason FROM {$table_name} ORDER BY exception_date ASC";
		$results    = $wpdb->get_results( $query, ARRAY_A );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Validate date values in Y-m-d format.
	 *
	 * @param string $value Raw date value.
	 * @return bool
	 */
	private static function is_valid_date( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}

		$date = DateTime::createFromFormat( 'Y-m-d', $value );

		return $date && $date->format( 'Y-m-d' ) === $value;
	}

	/**
	 * Redirect to settings page with a message code.
	 *
	 * @param string $notice Notice code.
	 * @return void
	 */
	private static function redirect_with_notice( $notice ) {
		$target_url = add_query_arg(
			array(
				'page'       => 'zeka-appointment-booking-settings',
				'zab_notice' => sanitize_key( $notice ),
			),
			admin_url( 'options-general.php' )
		);

		wp_safe_redirect( $target_url );
		exit;
	}

	/**
	 * Validate HH:MM values used by time fields.
	 *
	 * @param string $value Raw input value.
	 * @return string
	 */
	private static function sanitize_time_value( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( 1 === preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Check that a time range is valid and in ascending order.
	 *
	 * @param string $start_time Start time in HH:MM.
	 * @param string $end_time End time in HH:MM.
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
}
