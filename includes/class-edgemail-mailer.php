<?php
/**
 * Mailer class for EdgeMail.
 *
 * Intercepts wp_mail() calls and sends emails via Cloudflare Worker endpoint.
 *
 * @package EdgeMail
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDGEMAIL_Mailer class.
 */
class EDGEMAIL_Mailer {

	/**
	 * Constructor.
	 *
	 * Hook into pre_wp_mail filter.
	 */
	public function __construct() {
		// Hook into pre_wp_mail to intercept email sending.
		// This filter was introduced in WordPress 5.7.
		add_filter( 'pre_wp_mail', array( $this, 'intercept_email' ), 10, 2 );
	}

	/**
	 * Intercept wp_mail() calls and send via Worker.
	 *
	 * @param null|bool $return  Whether to short-circuit wp_mail(). Default null.
	 * @param array      $atts    Email parameters (to, subject, message, headers, attachments).
	 * @return null|bool True to short-circuit, null to proceed with default wp_mail().
	 */
	public function intercept_email( $return, $atts ) {
		// Get EdgeMail settings.
		$settings = get_option( 'edgemail_settings', array() );

		// If EdgeMail is not configured, fall back to default wp_mail().
		if ( empty( $settings['worker_url'] ) || empty( $settings['api_token'] ) ) {
			return null;
		}

		// Extract email parameters.
		$to       = isset( $atts['to'] ) ? $atts['to'] : '';
		$subject  = isset( $atts['subject'] ) ? $atts['subject'] : '';
		$message   = isset( $atts['message'] ) ? $atts['message'] : '';
		$headers   = isset( $atts['headers'] ) ? $atts['headers'] : array();
		$attachments = isset( $atts['attachments'] ) ? $atts['attachments'] : array();

		// Note: Attachments are not supported in v1.
		// They are ignored when sending to the Worker.

		// Parse from email/name from headers or use defaults.
		$from_email = $settings['default_from_email'] ?? '';
		$from_name  = $settings['default_from_name'] ?? '';

		// Check headers for From field.
		foreach ( $headers as $header ) {
			if ( is_string( $header ) && stripos( $header, 'From:' ) === 0 ) {
				// Parse From header: "From: Name <email@example.com>" or "From: email@example.com".
				$from_header = trim( substr( $header, 5 ) );
				if ( preg_match( '/^(.+?)\s*<(.+?)>$/', $from_header, $matches ) ) {
					$from_name  = trim( $matches[1], ' "\'' );
					$from_email = trim( $matches[2] );
				} else {
					$from_email = trim( $from_header );
				}
				break;
			}
		}

		// Validate from email.
		if ( empty( $from_email ) || ! is_email( $from_email ) ) {
			// If no valid from email, we can't send. Log and fallback.
			EDGEMAIL_Logger::log_event(
				$to,
				$subject,
				'error',
				0,
				'No valid From email address'
			);
			return null;
		}

		// Build payload matching Cloudflare Email Service format.
		$payload = array(
			'to'    => array( array( 'email' => $to ) ),
			'from'  => array(
				'email' => $from_email,
				'name'  => $from_name,
			),
			'subject' => $subject,
			'html'  => $message,
			'text'  => wp_strip_all_tags( $message ),
		);

		/**
		 * Filter the payload before sending to the Worker.
		 *
		 * @param array $payload Email payload.
		 * @param array $atts    Original email parameters.
		 */
		$payload = apply_filters( 'edgemail_before_send_payload', $payload, $atts );

		// Send to Worker.
		$result = $this->send_to_worker( $settings['worker_url'], $settings['api_token'], $payload );

		// Log the result.
		$status      = 'success';
		$http_code   = 200;
		$log_message = '';

		if ( is_wp_error( $result ) ) {
			$status      = 'error';
			$http_code   = 0;
			$log_message = $result->get_error_message();
		} else {
			$http_code   = wp_remote_retrieve_response_code( $result );
			$log_message = wp_remote_retrieve_body( $result );

			// Treat non-2xx responses as errors.
			if ( ! in_array( intval( $http_code ), array( 200, 201, 202, 204 ), true ) ) {
				$status = 'error';
			}
		}

		EDGEMAIL_Logger::log_event(
			$to,
			$subject,
			$status,
			$http_code,
			$log_message
		);

		/**
		 * Action fired after sending email to Worker.
		 *
		 * @param array|WP_Error $result  Worker response or WP_Error.
		 * @param array          $payload Email payload that was sent.
		 */
		do_action( 'edgemail_send_result', $result, $payload );

		// If successful, return true to short-circuit wp_mail().
		if ( 'success' === $status ) {
			return true;
		}

		// On failure, return null to allow default wp_mail() to proceed as fallback.
		return null;
	}

	/**
	 * Send email payload to Cloudflare Worker.
	 *
	 * @param string $worker_url Worker endpoint URL.
	 * @param string $api_token  API token for authentication.
	 * @param array  $payload    Email payload.
	 * @return array|WP_Error HTTP response or WP_Error on failure.
	 */
	private function send_to_worker( $worker_url, $api_token, $payload ) {
		$args = array(
			'method'  => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Content-Type'      => 'application/json',
				'X-EDGEMAIL-TOKEN' => $api_token,
			),
			'body'    => wp_json_encode( $payload ),
		);

		return wp_remote_post( $worker_url, $args );
	}
}
