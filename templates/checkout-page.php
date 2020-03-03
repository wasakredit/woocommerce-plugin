<?php
/**
 * Template Name: Wasa Kredit Checkout Template
 *
 * @package Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout
 * @since Wasa_Kredit_Checkout 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$order_key = sanitize_key( wp_unslash( $_GET['wasa_kredit_checkout'] ) ); // @codingStandardsIgnoreLine - Validation okay. Will exit further down if order is not found.

if ( ! isset( $order_key ) || empty( $order_key ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

$settings = get_option( 'wasa_kredit_settings' );

// Connect WASA SDK client
$client = new Sdk\Client(
	$settings['partner_id'],
	$settings['client_secret'],
	'yes' === $settings['test_mode'] ? true : false
);

// Collect data about order
$order_id = wc_get_order_id_by_order_key( $order_key );
$order    = wc_get_order( $order_id );

if ( ! $order ) {
	exit();
}

$order_data      = $order->get_data();
$currency        = get_woocommerce_currency();
$shipping_cost   = $order_data['shipping_total'];
$cart_items      = WC()->cart->get_cart();
$wasa_cart_items = array();

foreach ( $cart_items as $cart_item_key => $cart_item ) {
	$product = apply_filters(
		'woocommerce_cart_item_product',
		$cart_item['data'],
		$cart_item,
		$cart_item_key
	);

  $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );

  if ( !empty( $tax_rates ) ) {
    $tax_rate = reset( $tax_rates )['rate'];
  } else {
    $tax_rate = "0";
  }

  $id              = $cart_item['product_id'];
  $name            = $product->get_name();
  $price_inc_vat   = round(wc_get_price_including_tax( $product ), 2);
  $price_ex_vat    = round(wc_get_price_excluding_tax( $product ), 2);
  $vat_percentage  = round($tax_rate);
  $price_vat       = round(($price_inc_vat - $price_ex_vat), 2);
  $shipping_ex_vat = round($shipping_cost, 2);
  $quantity        = $cart_item['quantity'];

	//product bundles extension can generate negative price for one of the bundle products, here is how to fix it
	if ($price_ex_vat < 0) { $price_ex_vat = 0;}
	if ($price_vat < 0) { $price_vat = 0;}

	$wasa_cart_items[] = array(
		'product_id'     => $id,
		'product_name'   => $name,
		'price_ex_vat'   => array(
			'amount'   => $price_ex_vat,
			'currency' => $currency,
		),
		'quantity'       => $quantity,
		'vat_percentage' => $vat_percentage,
		'vat_amount'     => array(
			'amount'   => $price_vat,
			'currency' => $currency,
		),
	);
}

// Create payload from collected data
$payload = array(
	'purchaser_name'            => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
	'purchaser_email'           => $order->get_billing_email(),
	'purchaser_phone'           => $order->get_billing_phone(),
	'recipient_name'            => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
	'recipient_phone'           => $order->get_billing_phone(),
	'billing_address'           => array(
		'company_name'   => $order->get_billing_company(),
		'street_address' => $order->get_billing_address_1(),
		'postal_code'    => $order->get_billing_postcode(),
		'city'           => $order->get_billing_city(),
		'country'        => $order->get_billing_country(),
	),
	'delivery_address'          => array(
		'company_name'   => $order->get_shipping_company(),
		'street_address' => $order->get_shipping_address_1(),
		'postal_code'    => $order->get_shipping_postcode(),
		'city'           => $order->get_shipping_city(),
		'country'        => $order->get_shipping_country(),
	),
	'order_references'          => array(
		array(
			'key'   => 'wasa_kredit_woocommerce_order_key',
			'value' => $order->get_order_key(),
		),
	),
	'cart_items'                => $wasa_cart_items,
	'shipping_cost_ex_vat'      => array(
		'amount'   => $shipping_ex_vat,
		'currency' => $currency,
	),
	'request_domain'            => get_site_url(),
	'confirmation_callback_url' => $order->get_checkout_order_received_url(),
	'ping_url'                  => get_rest_url( null, 'wasa-kredit-checkout/v1/update_order_status' ),
);

// Get answer from API
$response = $client->create_checkout( $payload );

get_header();
?>

<style>
	.entry-title {
		display: none;
	}
	.woocommerce-breadcrumb {
		margin-bottom: 0;
	}
</style>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<div class="wasa-checkout">

		<?php
		if ( 201 === $response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
			echo $response->data; // @codingStandardsIgnoreLine - Echo out the HTML response from our backend
		} else {
			echo '<p><strong style="color: red">' . esc_html( 'Something went wrong while contacting Wasa Kredit API.' ) . '</strong></p>';

			if ( 'yes' === $settings['test_mode'] ) {
				echo '<hr/>';
				echo '<h4>Request to API</h4>';
				echo '<pre>' . wp_json_encode( $payload, JSON_PRETTY_PRINT ) . '</pre>';
				echo '<hr/>';
				echo '<h4>Response from Api</h4>';
				echo '<p>Wasa Kredit checkout is currently set to run in test mode, the plugin will echo out the error response from the Wasa Kredit Checkout API for easier debugging.</p>';
				echo '<ul>';
				echo '<li><strong>Status Code:</strong> ' . esc_html( $response->statusCode ) . '</li>'; // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
				echo '<li><strong>Error:</strong> ' . esc_html( $response->error ) . '</li>';
				echo '</ul>';
				echo '<pre>' . wp_json_encode( $response->data, JSON_PRETTY_PRINT ) . '</pre>';
				echo '<hr/>';
			}
		}
		?>
		</div>
	</main>
	</div>

	<script>
		<?php
			$confirm_url = add_query_arg(
				array(
					'key'                  => $order_key,
					'wasa_kredit_order_id' => '',
				),
				get_site_url( null, '/wc-api/wasa-order-payment-complete' )
			);

			$cancel_url = wc_get_checkout_url();
			?>
		var options = {
			onComplete: function ( orderReferences ) {
			// Update order to Processing
			var wasaKreditOrderId = '';
			for ( i = 0; i < orderReferences.length; i++ ) {
				if ( orderReferences[i].key === 'wasakredit-order-id' ){
					wasaKreditOrderId = orderReferences[i].value;
				}
			}

			var url = '<?php echo $confirm_url; // @codingStandardsIgnoreLine - Url with Query parameters pointing towards custom endpoint ?>' + '=' + wasaKreditOrderId;

			jQuery.ajax(url);
			window.location.href = '<?php echo $order->get_checkout_order_received_url();  // @codingStandardsIgnoreLine - Proceed Url with Query parameters ?>';
			},
			onCancel: function () {
				var checkoutUrl = '<?php echo esc_url( $cancel_url ); ?>';
				window.location.href = checkoutUrl;
			}
		};
		window.wasaCheckout.init( options );
	</script>

<?php
get_footer();
