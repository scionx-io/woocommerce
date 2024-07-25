# Scionx WooCommerce Payment Gateway Plugin

## Overview

Scionx WooCommerce Payment Gateway Plugin enables WooCommerce stores to accept payments using the Scionx payment gateway.

## Features

- Seamless integration with WooCommerce
- Secure and reliable payment processing
- Support for multiple currencies
- Detailed transaction logs

## Installation

1. Download the plugin zip file.
2. Navigate to your WordPress admin dashboard.
3. Go to Plugins > Add New.
4. Click on "Upload Plugin" and select the downloaded zip file.
5. Click "Install Now" and then "Activate" the plugin.

## Configuration

1. Navigate to WooCommerce > Settings > Payments.
2. Click on "Scionx Payment Gateway".
3. Configure the settings:
   - **Enable/Disable**: Check this to enable Scionx payment gateway.
   - **Title**: Enter the title for the payment method displayed to customers.
   - **Description**: Enter the description for the payment method displayed to customers.
   - **API Key**: Enter your Scionx API key.
4. Generate the webhook URL in WooCommerce:
   - Navigate to WooCommerce > Settings > Advanced > Webhooks.
   - Click on "Add Webhook".
   - Set the webhook topic to "Order Updated" and delivery URL to `https://yourdomain.com/wc-api/scionx`.
   - Save the webhook.
5. Copy the generated webhook URL.
6. Log in to your Scionx admin dashboard.
7. Navigate to Webhooks settings.
8. Paste the copied webhook URL and save.

## Usage

Once configured, customers can select Scionx as a payment method during checkout. Transactions will be processed securely through the Scionx gateway.

## Support

For support and troubleshooting, contact us at support@scionx.com.

## Changelog

### v1.0.0
- Initial release

## License

This plugin is licensed under the GPLv3. For more details, see the [LICENSE](LICENSE) file.

## Contributing

Contributions are welcome! Please submit a pull request or report issues on the [GitHub repository](https://github.com/scionx/scionx-woocommerce-plugin).
