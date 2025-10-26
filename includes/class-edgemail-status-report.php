<?php
/**
 * Status report class for EdgeMail.
 *
 * Adds EdgeMail section to WooCommerce Status page.
 *
 * @package EdgeMail
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDGEMAIL_Status_Report class.
 */
class EDGEMAIL_Status_Report {

	/**
	 * Constructor.
	 *
	 * Add status report section if WooCommerce is active.
	 */
	public function __construct() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'woocommerce_system_status_report', array( $this, 'render_status_report' ) );
		}
	}

	/**
	 * Render EdgeMail status report section.
	 */
	public function render_status_report() {
		$settings = get_option( 'edgemail_settings', array() );

		// Get configuration status.
		$worker_url_set    = ! empty( $settings['worker_url'] );
		$api_token_set     = ! empty( $settings['api_token'] );
		$from_email_set    = ! empty( $settings['default_from_email'] );
		$from_name_set     = ! empty( $settings['default_from_name'] );
		$is_configured     = $worker_url_set && $api_token_set && $from_email_set;

		// Get recent logs.
		$recent_logs = EDGEMAIL_Logger::get_recent_logs( 5 );

		// Get last test result.
		$last_test = EDGEMAIL_Logger::get_last_test_result();
		?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2" data-export-label="EdgeMail"><h2><?php esc_html_e( 'EdgeMail', 'edgemail' ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td data-export-label="Configuration"><?php esc_html_e( 'Configuration', 'edgemail' ); ?></td>
					<td>
						<?php
						$status_icon = $is_configured ? 'yes' : 'error';
						echo '<mark class="' . esc_attr( $status_icon ) . '"><span class="woocommerce-' . esc_attr( $status_icon ) . '">' . esc_html( $is_configured ? __( 'Configured', 'edgemail' ) : __( 'Not Configured', 'edgemail' ) ) . '</span></mark>';
						?>
					</td>
				</tr>
				<tr>
					<td data-export-label="Worker URL"><?php esc_html_e( 'Worker URL', 'edgemail' ); ?></td>
					<td>
						<?php
						if ( $worker_url_set ) {
							echo '<mark class="yes"><span class="woocommerce-yes">' . esc_html__( 'Set', 'edgemail' ) . '</span></mark>';
						} else {
							echo '<mark class="error"><span class="woocommerce-error">' . esc_html__( 'Not Set', 'edgemail' ) . '</span></mark>';
						}
						?>
					</td>
				</tr>
				<tr>
					<td data-export-label="API Token"><?php esc_html_e( 'API Token', 'edgemail' ); ?></td>
					<td>
						<?php
						if ( $api_token_set ) {
							echo '<mark class="yes"><span class="woocommerce-yes">' . esc_html__( 'Set', 'edgemail' ) . '</span></mark>';
						} else {
							echo '<mark class="error"><span class="woocommerce-error">' . esc_html__( 'Not Set', 'edgemail' ) . '</span></mark>';
						}
						?>
					</td>
				</tr>
				<tr>
					<td data-export-label="Default From Email"><?php esc_html_e( 'Default From Email', 'edgemail' ); ?></td>
					<td>
						<?php
						if ( $from_email_set ) {
							echo '<mark class="yes"><span class="woocommerce-yes">' . esc_html( $settings['default_from_email'] ) . '</span></mark>';
						} else {
							echo '<mark class="error"><span class="woocommerce-error">' . esc_html__( 'Not Set', 'edgemail' ) . '</span></mark>';
						}
						?>
					</td>
				</tr>
				<?php if ( $last_test ) : ?>
				<tr>
					<td data-export-label="Last Test Email"><?php esc_html_e( 'Last Test Email', 'edgemail' ); ?></td>
					<td>
						<?php
						$test_status = 'success' === $last_test->status ? 'yes' : 'error';
						$test_date   = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_test->sent_at ) );
						?>
						<mark class="<?php echo esc_attr( $test_status ); ?>">
							<span class="woocommerce-<?php echo esc_attr( $test_status ); ?>">
								<?php
								echo esc_html( ucfirst( $last_test->status ) );
								echo ' - ';
								echo esc_html( $test_date );
								?>
							</span>
						</mark>
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( ! empty( $recent_logs ) ) : ?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="5" data-export-label="EdgeMail Recent Logs"><h2><?php esc_html_e( 'EdgeMail Recent Logs', 'edgemail' ); ?></h2></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Date', 'edgemail' ); ?></th>
					<th><?php esc_html_e( 'To', 'edgemail' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'edgemail' ); ?></th>
					<th><?php esc_html_e( 'Status', 'edgemail' ); ?></th>
					<th><?php esc_html_e( 'HTTP Code', 'edgemail' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_logs as $log ) : ?>
				<tr>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->sent_at ) ) ); ?></td>
					<td><?php echo esc_html( $log->to_email ); ?></td>
					<td><?php echo esc_html( $log->subject ); ?></td>
					<td>
						<?php
						$status_class = 'success' === $log->status ? 'yes' : 'error';
						echo '<mark class="' . esc_attr( $status_class ) . '"><span class="woocommerce-' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( $log->status ) ) . '</span></mark>';
						?>
					</td>
					<td><?php echo esc_html( $log->http_code ? $log->http_code : '—' ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
		<p><?php esc_html_e( 'No email logs yet.', 'edgemail' ); ?></p>
		<?php endif; ?>
		<?php
	}
}
