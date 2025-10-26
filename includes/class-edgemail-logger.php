<?php
/**
 * Logger class for EdgeMail.
 *
 * Handles database table creation and logging of email send attempts.
 *
 * @package EdgeMail
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDGEMAIL_Logger class.
 */
class EDGEMAIL_Logger {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	const TABLE_NAME = 'edgemail_logs';

	/**
	 * Create the logs table.
	 *
	 * Called on plugin activation.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sent_at datetime NOT NULL,
			to_email varchar(512) NOT NULL,
			subject text NOT NULL,
			status varchar(20) NOT NULL,
			http_code int(11) DEFAULT NULL,
			worker_response text,
			PRIMARY KEY (id),
			KEY sent_at (sent_at),
			KEY status (status)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Log an email send attempt.
	 *
	 * @param string $to_email Recipient email address.
	 * @param string $subject  Email subject.
	 * @param string $status   'success' or 'error'.
	 * @param int    $http_code HTTP response code from Worker.
	 * @param string $response  Worker response body or error message.
	 */
	public static function log_event( $to_email, $subject, $status, $http_code = null, $response = '' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table_name,
			array(
				'sent_at'        => current_time( 'mysql' ),
				'to_email'       => sanitize_text_field( $to_email ),
				'subject'         => sanitize_text_field( $subject ),
				'status'          => sanitize_text_field( $status ),
				'http_code'       => $http_code ? intval( $http_code ) : null,
				'worker_response' => sanitize_text_field( $response ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s' )
		);
	}

	/**
	 * Get recent log entries.
	 *
	 * @param int $limit Number of entries to retrieve. Default 5.
	 * @return array Array of log entries.
	 */
	public static function get_recent_logs( $limit = 5 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$limit = absint( $limit );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY sent_at DESC LIMIT %d",
				$limit
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Get the last test email result.
	 *
	 * @return object|false Log entry object or false if not found.
	 */
	public static function get_last_test_result() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Look for test emails (those sent to admin email).
		$admin_email = get_option( 'admin_email' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result      = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE to_email = %s ORDER BY sent_at DESC LIMIT 1",
				$admin_email
			)
		);

		return $result ? $result : false;
	}
}
