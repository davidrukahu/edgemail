<?php
/**
 * Plugin Name: EdgeMail
 * Plugin URI: https://github.com/davidrukahu/edgemail
 * Description: Replace WordPress transactional email with Cloudflare Worker endpoint integration
 * Version: 1.0.0
 * Author: DavidR
 * Author URI: https://github.com/davidrukahu/edgemail
 * Text Domain: edgemail
 * Requires at least: 5.7
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package EdgeMail
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'EDGEMAIL_VERSION', '1.0.0' );
define( 'EDGEMAIL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDGEMAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EDGEMAIL_PLUGIN_FILE', __FILE__ );

// Require PHP 7.4+.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'EdgeMail requires PHP 7.4 or higher. Please update PHP.', 'edgemail' ); ?></p>
			</div>
			<?php
		}
	);
	return;
}

/**
 * Plugin activation hook.
 *
 * Creates the database table for logging.
 */
function edgemail_activate() {
	require_once EDGEMAIL_PLUGIN_DIR . 'includes/class-edgemail-logger.php';
	EDGEMAIL_Logger::create_table();
}

register_activation_hook( __FILE__, 'edgemail_activate' );

/**
 * Load plugin classes and initialize.
 */
function edgemail_init() {
	// Load classes.
	require_once EDGEMAIL_PLUGIN_DIR . 'includes/class-edgemail-logger.php';
	require_once EDGEMAIL_PLUGIN_DIR . 'includes/class-edgemail-mailer.php';
	require_once EDGEMAIL_PLUGIN_DIR . 'includes/class-edgemail-settings.php';
	require_once EDGEMAIL_PLUGIN_DIR . 'includes/helpers.php';

	// Initialize mailer.
	new EDGEMAIL_Mailer();

	// Initialize settings page.
	new EDGEMAIL_Settings();

	// Initialize WooCommerce status report if WooCommerce is active.
	if ( class_exists( 'WooCommerce' ) ) {
		require_once EDGEMAIL_PLUGIN_DIR . 'includes/class-edgemail-status-report.php';
		new EDGEMAIL_Status_Report();
	}
}

add_action( 'plugins_loaded', 'edgemail_init' );
