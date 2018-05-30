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

if ( ! $_GET['wasa_kredit_checkout'] || empty( $_GET['wasa_kredit_checkout'] ) ) {
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
$order_key = $_GET['wasa_kredit_checkout'];
$order_id  = wc_get_order_id_by_order_key( $order_key );
$order     = wc_get_order( $order_id );

if ( ! $order ) {
	exit();
}

$order_data      = $order->get_data();
$currency        = get_woocommerce_currency();
$shipping_cost   = $order_data['shipping_total'];
$shipping_tax    = $order_data['shipping_tax'];
$cart_items      = WC()->cart->get_cart();
$wasa_cart_items = array();

foreach ( $cart_items as $cart_item_key => $cart_item ) {
	$product = apply_filters(
		'woocommerce_cart_item_product',
		$cart_item['data'],
		$cart_item,
		$cart_item_key
	);

	$id              = $cart_item['product_id'];
	$name            = $product->get_name();
	$price_inc_vat   = wc_get_price_including_tax( $product );
	$price_ex_vat    = wc_get_price_excluding_tax( $product );
	$vat_percentage  = ( $price_inc_vat > 0 ? ( $price_ex_vat / $price_inc_vat ) * 100 : 0 );
	$price_vat       = $price_inc_vat - $price_ex_vat;
	$shipping_ex_vat = $shipping_cost - $shipping_tax;
	$quantity        = $cart_item['quantity'];

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
	'ping_url'                  => get_site_url( null, '/wasa-kredit-checkout/order-update/' ),
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
		if ( 201 === $response->statusCode ) {
			echo $response->data;
		} else {
			echo '<p><strong style="color: red">' . __( 'Something went wrong while contacting Wasa Kredit API.' ) . '</strong></p>';

			if ( 'yes' === $settings['test_mode'] ) {
				echo '<hr/>';
				echo '<h4>Request to API</h4>';
				echo '<pre>' . json_encode( $payload, JSON_PRETTY_PRINT ) . '</pre>';
				echo '<hr/>';
				echo '<h4>Response from Api</h4>';
				echo '<p>Wasa Kredit checkout is currently set to run in test mode, the plugin will echo out the error response from the Wasa Kredit Checkout API for easier debugging.</p>';
				echo '<ul>';
				echo '<li><strong>Status Code:</strong> ' . $response->statusCode . '</li>';
				echo '<li><strong>Error:</strong> ' . $response->error . '</li>';
				echo '</ul>';
				echo '<pre>' . json_encode( $response->data, JSON_PRETTY_PRINT ) . '</pre>';
				echo '<hr/>';
			}
		}
		?>
		</div>
	</main>
	</div>

	<script>
		var options = {
			onComplete: function ( orderReferences ) {
			// Update order to Processing
			var wasaKreditOrderId = orderReferences[1].value;
			var url = '<?php echo get_site_url( null, '/wc-api/wasa-order-payment-complete?key=' . $order->get_order_key() . '&wasa_kredit_order_id=' ); ?>' + wasaKreditOrderId;

			jQuery.ajax(url);
			window.location.href = "<?php echo $order->get_checkout_order_received_url(); ?>";
			},
			onCancel: function () {
				var checkoutUrl = '<?php echo get_site_url( null, '/checkout/' ); ?>';

				window.location.href = checkoutUrl;
			}
		};
		window.wasaCheckout.init( options );
	</script>

<?php
get_footer();
