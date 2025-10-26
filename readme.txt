=== EdgeMail ===
Contributors: davidrukahu
Donate link: https://github.com/davidrukahu/edgemail
Tags: email, cloudflare, workers, transactional, woocommerce
Requires at least: 5.7
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Replace WordPress transactional email with Cloudflare Worker endpoint integration.

== Description ==

EdgeMail intercepts WordPress outbound email (wp_mail) and sends it via your Cloudflare Worker endpoint instead of using the default PHP mailer.

= Features =

* **Cloudflare Integration**: Send emails through your Cloudflare Worker using the Email Service API
* **Safe Fallback**: Automatically falls back to default WordPress email if Worker fails or plugin is not configured
* **Email Logging**: Track all email attempts with status, HTTP codes, and responses
* **Easy Configuration**: Simple settings page with Worker URL, API token, and default From fields
* **Test Email**: Send test emails to verify your configuration
* **WooCommerce Status**: View recent email logs and configuration status in WooCommerce → Status
* **Zero Downtime**: Unconfigured installations continue to work normally

= Cloudflare Email Service =

This plugin is designed to work with Cloudflare's Email Service, announced in September 2025. Your Cloudflare Worker endpoint should handle the email sending using the `env.SEND_EMAIL.send()` binding.

= Installation =

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools → EdgeMail in the WordPress admin
4. Configure your Worker URL, API token, and default From fields
5. Send a test email to verify your configuration

= Configuration =

In the EdgeMail settings page, you'll need to provide:

* **Worker URL**: Your Cloudflare Worker endpoint (e.g., https://your-worker.your-subdomain.workers.dev)
* **API Token**: Shared secret for authenticating with your Worker
* **Default From Name**: Default sender name for emails
* **Default From Email**: Default sender email (must be verified in Cloudflare Email Service)

= Limitations =

* Attachments are not supported in v1
* Requires WordPress 5.7+ (uses pre_wp_mail filter)

== Changelog ==

= 1.0.0 =
* Initial release
* Email interception via pre_wp_mail filter
* Cloudflare Worker integration
* Settings page with test email functionality
* Database logging
* WooCommerce Status integration

== Frequently Asked Questions ==

= What happens if my Worker is down? =

EdgeMail automatically falls back to the default WordPress email sending mechanism. Your site will continue to send emails normally.

= Can I use this without WooCommerce? =

Yes! EdgeMail works on any WordPress installation. The settings page appears under Tools in the WordPress admin.

= Do I need to create my own Cloudflare Worker? =

Yes, you'll need to create a Cloudflare Worker that receives the email payload and uses the Email Service API to send emails. See Cloudflare documentation for details.

== Screenshots ==

1. Settings page under Tools menu
2. WooCommerce Status integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of EdgeMail plugin.
