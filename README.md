# EdgeMail WordPress Plugin

Replace WordPress transactional email with Cloudflare Worker endpoint integration.

## Description

EdgeMail intercepts WordPress outbound email (`wp_mail()`) and sends it via your Cloudflare Worker endpoint instead of using the default PHP mailer. Built to work with [Cloudflare Email Service](https://blog.cloudflare.com/email-service/).

### Features

- **Cloudflare Integration**: Send emails through your Cloudflare Worker using the Email Service API
- **Safe Fallback**: Automatically falls back to default WordPress email if Worker fails or plugin is not configured
- **Email Logging**: Track all email attempts with status, HTTP codes, and responses
- **Easy Configuration**: Simple settings page with Worker URL, API token, and default From fields
- **Test Email**: Send test emails to verify your configuration
- **WooCommerce Status**: View recent email logs and configuration status in WooCommerce → Status
- **Zero Downtime**: Unconfigured installations continue to work normally

### Installation

1. Download or clone this repository
2. Upload to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **WooCommerce → EdgeMail** (or **EdgeMail** in the sidebar)
5. Configure your Worker URL, API token, and default From fields
6. Send a test email to verify your configuration

### Configuration

In the EdgeMail settings page, you'll need to provide:

- **Worker URL**: Your Cloudflare Worker endpoint (e.g., `https://your-worker.your-subdomain.workers.dev`)
- **API Token**: Shared secret for authenticating with your Worker
- **Default From Name**: Default sender name for emails
- **Default From Email**: Default sender email (must be verified in Cloudflare Email Service)

### Limitations

- Attachments are not supported in v1
- Requires WordPress 5.7+ (uses `pre_wp_mail` filter)

### Requirements

- WordPress 5.7 or higher
- PHP 7.4 or higher

### FAQ

**What happens if my Worker is down?**

EdgeMail automatically falls back to the default WordPress email sending mechanism. Your site will continue to send emails normally.

**Can I use this without WooCommerce?**

Yes! EdgeMail works on any WordPress installation. The settings page appears as a top-level menu if WooCommerce is not active.

**Do I need to create my own Cloudflare Worker?**

Yes, you'll need to create a Cloudflare Worker that receives the email payload and uses the Email Service API to send emails. See [Cloudflare documentation](https://blog.cloudflare.com/email-service/) for details.

### Changelog

#### 1.0.0
* Initial release
* Email interception via `pre_wp_mail` filter
* Cloudflare Worker integration
* Settings page with test email functionality
* Database logging
* WooCommerce Status integration

### License

GPLv2 or later

### Author

**DavidR** - [GitHub](https://github.com/davidrukahu)

### Credits

Built to integrate with [Cloudflare Email Service](https://blog.cloudflare.com/email-service/) announced in September 2025.
