<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Scionx_Gateway_Blocks_Support extends AbstractPaymentMethodType
{	
	private $gateway;
	protected $name = 'scionx';

	public function initialize()
	{
		$this->settings = get_option( 'woocommerce_scionx_settings', [] );
		$this->gateway = new WC_Scionx_Gateway();
	}

	public function is_active()
	{
		return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
	}

	public function get_payment_method_script_handles()
	{
		wp_register_script(
            'wc-scionx-blocks-integration',
            plugin_dir_url(__FILE__) . 'build/index.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );

		return array( 'wc-scionx-blocks-integration' );

	}

	public function get_payment_method_data()
	{
		$icon = plugins_url('/images/icon.png', __FILE__ );

		if (empty($this->get_setting( 'scionx_show_logo_icon' )) || $this->get_setting( 'scionx_show_logo_icon' ) == 'no')
		{
			$icon = '';
		}

		return array(
			'title'        => $this->get_setting( 'title' ),
			'description'  => $this->get_setting( 'description' ),
			'icon'         => $icon,
		);
	}
}