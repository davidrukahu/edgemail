<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package EdgeMail
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall.
 *
 * Note: We intentionally do NOT drop the wp_edgemail_logs table.
 * Email logs may be required for compliance or auditing purposes.
 * To drop the table, manually delete it from the database.
 *
 * TODO: Add a checkbox in settings to allow destructive uninstall.
 */
function edgemail_uninstall() {
	// Delete plugin options.
	delete_option( 'edgemail_settings' );

	// Note: We do NOT drop the edgemail_logs table.
	// This preserves email logs for auditing/compliance.
	//
	// To manually drop the table, run this SQL:
	// DROP TABLE IF EXISTS {prefix}edgemail_logs;
}

edgemail_uninstall();
