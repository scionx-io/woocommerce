<?php

/*
Plugin Name: WooCommerce Scionx Payment Gateway
Plugin URI: https://syedaqeeqabbas.com
Description: Take payments from Scionx payment gateway on woocommerce checkout.
Author: Syed Aqeeq Abbas
Author URI: https://syedaqeeqabbas.com
version: 1.0.0
*/

function misha_add_gateway_class( $gateways )
{
	$gateways[] = 'WC_Scionx_Gateway';

	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'misha_add_gateway_class' );

function scionx_init_gateway_class()
{
	class WC_Scionx_Gateway extends WC_Payment_Gateway
	{
		public function __construct()
		{
			$this->id = 'scionx';
			$this->icon = plugins_url('/images/icon.png', __FILE__ );
			$this->has_fields = false;
			$this->method_title = 'Scionx Gateway';
			$this->method_description = __( 'Configuration and setup options for Scionx payment gateway', 'woocommerce' );

			$this->init_form_fields();

			$this->init_settings();

			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			$this->enabled = $this->get_option( 'enabled' );
			$this->scionx_api_key = $this->get_option( 'scionx_api_key' );
			$this->scionx_token_symbol = $this->get_option( 'scionx_token_symbol' );
			$this->scionx_chain_id = $this->get_option( 'scionx_chain_id' );
			$this->scionx_mode = $this->get_option( 'scionx_mode' );

			if (!empty($this->scionx_mode))
			{
				$this->scionx_base_url = 'https://api.scionx.dev';
			}
			else
			{
				$this->scionx_base_url = 'https://api.scionx.io';
			}

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'woocommerce_api_' . $this->id, array( $this, 'webhook' ) );
		}

		public function init_form_fields()
		{
			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Enable Scionx', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title'              => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
					'default'     => __( 'Scionx', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'description'        => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce' ),
					'default'     => __( 'Pay with scionx payment method.', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'instructions'       => array(
					'title'       => __( 'Instructions', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
					'default'     => __( 'Pay with scionx payment method.', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'scionx_api_key' => array(
					'title'       => __( 'API Key', 'woocommerce' ),
					'type'        => 'text'
				),
				'scionx_token_symbol' => array(
					'title'       => __( 'Token Symbol', 'woocommerce' ),
					'type'        => 'text'
				),
				'scionx_chain_id' => array(
					'title'       => __( 'Chain ID', 'woocommerce' ),
					'type'        => 'text'
				),
				'scionx_mode' => array(
					'title'       => __( 'Staging mode', 'woocommerce' ),
					'label'       => __( 'Enable staging mode', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => __( 'Place the scionx payment gateway in staging mode using test API keys.', 'woocommerce' ),
					'default'     => 'yes',
					'desc_tip'    => true,
				),
			);
		}

		public function process_payment( $order_id )
		{
			global $woocommerce;

  			$order = wc_get_order( $order_id );

  			if (empty($order) || empty($this->scionx_token_symbol) || empty($this->scionx_chain_id) || empty($this->scionx_api_key))
  			{
  				return array(
				    'result'   => 'failure',
				    'messages' => __( 'Error processing checkout. Please try again.', 'woocommerce' )
				);
  			}

  			$params = [
  				'checkout' => [
  					'amount_in_cents' => round($order->get_total() * 100),
  					'token_symbol' => $this->scionx_token_symbol,
  					'chain_id' => $this->scionx_chain_id,
  					'metadata' => [
  						'order_id' => $order_id,
  						'customer_id' => $order_id,
  					],
  					'cancel_url' => wc_get_checkout_url(),
  					'success_url' => $this->get_return_url($order)
  				]
  			];

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  	CURLOPT_URL => $this->scionx_base_url . '/v1/checkouts',
			  	CURLOPT_RETURNTRANSFER => true,
			  	CURLOPT_ENCODING => '',
			  	CURLOPT_MAXREDIRS => 10,
			  	CURLOPT_TIMEOUT => 0,
			  	CURLOPT_FOLLOWLOCATION => true,
			  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  	CURLOPT_CUSTOMREQUEST => 'POST',
			  	CURLOPT_POSTFIELDS => json_encode($params),
			  	CURLOPT_HTTPHEADER => array(
				    'Content-Type: application/json',
				    'Authorization: Bearer ' . $this->scionx_api_key
			  	),
			));

			$response = curl_exec($curl);

			curl_close($curl);

			if (!empty($response))
			{
				$response = json_decode($response, true);

				if (isset($response['id']) && !empty($response['id']) && isset($response['url']) && !empty($response['url']))
				{
					return array(
					    'result' => 'success',
					    'redirect' => $response['url']
				  	);
				}
			}

			return array(
			    'result'   => 'failure',
			    'messages' => __( 'Error processing checkout. Please try again.', 'woocommerce' )
			);

		}

		public function webhook()
		{

		}
	}
}
add_action( 'plugins_loaded', 'scionx_init_gateway_class' );