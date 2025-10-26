/**
 * Admin JavaScript for EdgeMail.
 *
 * @package EdgeMail
 */
(function($) {
	'use strict';

	/**
	 * Handle test email button click.
	 */
	function handleTestEmail() {
		var $button = $('#edgemail-send-test');
		var $result = $('#edgemail-test-result');

		if ($button.attr('disabled')) {
			return;
		}

		// Disable button and show loading state.
		$button.attr('disabled', true);
		$result
			.removeClass('success error')
			.addClass('loading')
			.html('<strong>' + edgemailAdmin.strings.sending + '</strong>')
			.show();

		// Send AJAX request.
		$.ajax({
			url: edgemailAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'edgemail_send_test_email',
				nonce: edgemailAdmin.nonce
			},
			success: function(response) {
				$result.removeClass('loading');

				if (response.success) {
					$result
						.removeClass('error')
						.addClass('success')
						.html('<strong>' + edgemailAdmin.strings.success + '</strong>');
				} else {
					$result
						.removeClass('success')
						.addClass('error')
						.html('<strong>' + edgemailAdmin.strings.error + '</strong>');
				}
			},
			error: function() {
				$result
					.removeClass('loading success')
					.addClass('error')
					.html('<strong>' + edgemailAdmin.strings.error + '</strong>');
			},
			complete: function() {
				$button.attr('disabled', false);
			}
		});
	}

	/**
	 * Initialize on document ready.
	 */
	$(document).ready(function() {
		// Attach event handler.
		$('#edgemail-send-test').on('click', handleTestEmail);
	});

})(jQuery);
