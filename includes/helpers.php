<?php
/**
 * Helper functions for EdgeMail.
 *
 * Utility functions used throughout the plugin.
 *
 * @package EdgeMail
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if EdgeMail is configured.
 *
 * @return bool True if Worker URL and API token are both set.
 */
if ( ! function_exists( 'edgemail_is_configured' ) ) {
	function edgemail_is_configured() {
		$settings = get_option( 'edgemail_settings', array() );
		return ! empty( $settings['worker_url'] ) && ! empty( $settings['api_token'] );
	}
}

/**
 * Get EdgeMail settings.
 *
 * @return array EdgeMail settings array.
 */
if ( ! function_exists( 'edgemail_get_settings' ) ) {
	function edgemail_get_settings() {
		return get_option( 'edgemail_settings', array() );
	}
}

/**
 * Strip HTML from message to create plain text version.
 *
 * @param string $html HTML message content.
 * @return string Plain text version.
 */
if ( ! function_exists( 'edgemail_strip_html' ) ) {
	function edgemail_strip_html( $html ) {
		return wp_strip_all_tags( $html );
	}
}
