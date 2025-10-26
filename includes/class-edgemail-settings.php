<?php
/**
 * Settings page class for EdgeMail.
 *
 * Provides admin interface for configuring EdgeMail settings.
 *
 * @package EdgeMail
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDGEMAIL_Settings class.
 */
class EDGEMAIL_Settings {

	/**
	 * Constructor.
	 *
	 * Set up hooks for admin menu and settings.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_edgemail_send_test_email', array( $this, 'handle_test_email' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		// Check if WooCommerce is active.
		if ( class_exists( 'WooCommerce' ) ) {
			// Add as WooCommerce submenu.
			add_submenu_page(
				'woocommerce',
				__( 'EdgeMail', 'edgemail' ),
				__( 'EdgeMail', 'edgemail' ),
				'manage_options',
				'edgemail',
				array( $this, 'render_settings_page' )
			);
		} else {
			// Add as top-level menu.
			add_menu_page(
				__( 'EdgeMail', 'edgemail' ),
				__( 'EdgeMail', 'edgemail' ),
				'manage_options',
				'edgemail',
				array( $this, 'render_settings_page' ),
				'dashicons-email-alt2'
			);
		}
	}

	/**
	 * Register settings with WordPress Settings API.
	 */
	public function register_settings() {
		register_setting(
			'edgemail_settings',
			'edgemail_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'edgemail_main_section',
			__( 'Configuration', 'edgemail' ),
			array( $this, 'render_section_description' ),
			'edgemail'
		);

		add_settings_field(
			'worker_url',
			__( 'Worker URL', 'edgemail' ),
			array( $this, 'render_worker_url_field' ),
			'edgemail',
			'edgemail_main_section'
		);

		add_settings_field(
			'api_token',
			__( 'API Token / Secret', 'edgemail' ),
			array( $this, 'render_api_token_field' ),
			'edgemail',
			'edgemail_main_section'
		);

		add_settings_field(
			'default_from_name',
			__( 'Default From Name', 'edgemail' ),
			array( $this, 'render_from_name_field' ),
			'edgemail',
			'edgemail_main_section'
		);

		add_settings_field(
			'default_from_email',
			__( 'Default From Email', 'edgemail' ),
			array( $this, 'render_from_email_field' ),
			'edgemail',
			'edgemail_main_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['worker_url'] ) ) {
			$sanitized['worker_url'] = esc_url_raw( $input['worker_url'] );
		}

		if ( isset( $input['api_token'] ) ) {
			// Preserve existing token if field is masked.
			if ( strpos( $input['api_token'], '••••' ) === 0 ) {
				$existing = get_option( 'edgemail_settings', array() );
				$sanitized['api_token'] = isset( $existing['api_token'] ) ? $existing['api_token'] : '';
			} else {
				$sanitized['api_token'] = sanitize_text_field( $input['api_token'] );
			}
		}

		if ( isset( $input['default_from_name'] ) ) {
			$sanitized['default_from_name'] = sanitize_text_field( $input['default_from_name'] );
		}

		if ( isset( $input['default_from_email'] ) ) {
			$sanitized['default_from_email'] = sanitize_email( $input['default_from_email'] );
		}

		return $sanitized;
	}

	/**
	 * Render section description.
	 */
	public function render_section_description() {
		?>
		<p><?php esc_html_e( 'Configure EdgeMail to send transactional emails via your Cloudflare Worker endpoint.', 'edgemail' ); ?></p>
		<?php
	}

	/**
	 * Render Worker URL field.
	 */
	public function render_worker_url_field() {
		$settings = get_option( 'edgemail_settings', array() );
		$value    = isset( $settings['worker_url'] ) ? $settings['worker_url'] : '';
		?>
		<input type="url" name="edgemail_settings[worker_url]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="https://your-worker.your-subdomain.workers.dev" />
		<p class="description"><?php esc_html_e( 'The HTTPS endpoint of your Cloudflare Worker.', 'edgemail' ); ?></p>
		<?php
	}

	/**
	 * Render API Token field.
	 */
	public function render_api_token_field() {
		$settings = get_option( 'edgemail_settings', array() );
		$value    = isset( $settings['api_token'] ) ? $settings['api_token'] : '';

		// Mask the token in the UI.
		$display_value = '';
		if ( ! empty( $value ) ) {
			$display_value = '••••' . substr( $value, -4 );
		}
		?>
		<input type="password" name="edgemail_settings[api_token]" value="<?php echo esc_attr( $display_value ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Enter API token', 'edgemail' ); ?>" />
		<p class="description"><?php esc_html_e( 'Shared secret for authenticating with your Worker. This is stored in plain text in the database.', 'edgemail' ); ?></p>
		<?php
	}

	/**
	 * Render From Name field.
	 */
	public function render_from_name_field() {
		$settings = get_option( 'edgemail_settings', array() );
		$value    = isset( $settings['default_from_name'] ) ? $settings['default_from_name'] : '';
		?>
		<input type="text" name="edgemail_settings[default_from_name]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Default sender name for emails.', 'edgemail' ); ?></p>
		<?php
	}

	/**
	 * Render From Email field.
	 */
	public function render_from_email_field() {
		$settings = get_option( 'edgemail_settings', array() );
		$value    = isset( $settings['default_from_email'] ) ? $settings['default_from_email'] : '';
		?>
		<input type="email" name="edgemail_settings[default_from_email]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Default sender email address. Must be verified in your Cloudflare Email Service.', 'edgemail' ); ?></p>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'edgemail' ) );
		}
		?>
		<div class="wrap edgemail-settings">
			<h1><?php esc_html_e( 'EdgeMail Settings', 'edgemail' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'edgemail_settings' );
				do_settings_sections( 'edgemail' );
				submit_button();
				?>
			</form>

			<div class="edgemail-test-section">
				<h2><?php esc_html_e( 'Send Test Email', 'edgemail' ); ?></h2>
				<p><?php esc_html_e( 'Send a test email to verify your EdgeMail configuration.', 'edgemail' ); ?></p>
				<button type="button" id="edgemail-send-test" class="button button-secondary">
					<?php esc_html_e( 'Send Test Email', 'edgemail' ); ?>
				</button>
				<div id="edgemail-test-result"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle test email AJAX request.
	 */
	public function handle_test_email() {
		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'edgemail' ) );
		}

		// Verify nonce.
		check_admin_referer( 'edgemail_send_test_email', 'nonce' );

		// Get admin email.
		$admin_email = get_option( 'admin_email' );

		// Send test email.
		$subject = __( 'EdgeMail test email', 'edgemail' );
		$message = __( 'This is a test email from EdgeMail. If you received this, your configuration is working correctly!', 'edgemail' );

		$sent = wp_mail(
			$admin_email,
			$subject,
			$message
		);

		if ( $sent ) {
			wp_send_json_success( array( 'message' => __( 'Test email sent successfully!', 'edgemail' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send test email.', 'edgemail' ) ) );
		}
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_edgemail' !== $hook && 'woocommerce_page_edgemail' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'edgemail-admin',
			EDGEMAIL_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			EDGEMAIL_VERSION,
			true
		);

		wp_enqueue_style(
			'edgemail-admin',
			EDGEMAIL_PLUGIN_URL . 'assets/admin.css',
			array(),
			EDGEMAIL_VERSION
		);

		// Localize script with nonce and AJAX URL.
		wp_localize_script(
			'edgemail-admin',
			'edgemailAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'edgemail_send_test_email' ),
				'strings' => array(
					'sending' => esc_html__( 'Sending...', 'edgemail' ),
					'success' => esc_html__( 'Test email sent successfully!', 'edgemail' ),
					'error'   => esc_html__( 'Failed to send test email.', 'edgemail' ),
				),
			)
		);
	}
}
