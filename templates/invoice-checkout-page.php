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

$order_key = filter_input( INPUT_GET, 'wasa_kredit_checkout', FILTER_SANITIZE_STRING );

if ( ! isset( $order_key ) || empty( $order_key ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../includes/class-wasa-kredit-checkout-sdk-helper.php';

$settings = get_option( 'wasa_kredit_settings' );

// Connect WASA SDK client
$client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();

// Collect data about order
$order_id = wc_get_order_id_by_order_key( $order_key );
$order    = wc_get_order( $order_id );

if ( ! $order ) {
	exit();
}

$order_data      = $order->get_data();
$shipping_ex_vat = number_format( $order_data['shipping_total'], 2, '.', '' );
$shipping_vat    = $order_data['shipping_tax'];
$cart_items      = WC()->cart->get_cart();
$wasa_cart_items = array();

function apply_currency( $amount ) {
	return array(
		'amount'   => $amount,
		'currency' => get_woocommerce_currency(),
	);
}

foreach ( $cart_items as $cart_item_key => $cart_item ) {
	$product = apply_filters(
		'woocommerce_cart_item_product',
		$cart_item['data'],
		$cart_item,
		$cart_item_key
	);

	$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );

	if ( ! empty( $tax_rates ) ) {
		$tax_rate = reset( $tax_rates )['rate'];
	} else {
		$tax_rate = '0';
	}

	$id   = $cart_item['product_id'];
	$name = $product->get_name();

	$price_inc_vat  = number_format( wc_get_price_including_tax( $product ), 2, '.', '' );
	$price_ex_vat   = number_format( wc_get_price_excluding_tax( $product ), 2, '.', '' );
	$vat_percentage = round( $tax_rate );
	$price_vat      = number_format( $price_inc_vat - $price_ex_vat, 2, '.', '' );
	$quantity       = $cart_item['quantity'];

	array_push(
		$wasa_cart_items,
		array(
			'product_id'           => $id,
			'product_name'         => $name,
			'price_ex_vat'         => apply_currency( $price_ex_vat ),
			'price_incl_vat'       => apply_currency( $price_inc_vat ),
			'quantity'             => $quantity,
			'vat_percentage'       => $vat_percentage,
			'vat_amount'           => apply_currency( $price_vat ),
			'total_price_ex_vat'   => apply_currency( number_format( $price_ex_vat * $quantity, 2, '.', '' ) ),
			'total_price_incl_vat' => apply_currency( number_format( $price_inc_vat * $quantity, 2, '.', '' ) ),
			'total_vat'            => apply_currency( number_format( $price_vat * $quantity, 2, '.', '' ) ),
		)
	);
}

// Create an array of all tax rates
$all_tax_rates = array_replace(
	WC_Tax::get_rates(),
	...array_map(
		function ( $tc ) {
			return WC_Tax::get_rates( $tc );
		},
		WC_Tax::get_tax_classes()
	)
);

// Add shipping cost for all shipping lines
foreach ( $order->get_data()['shipping_lines'] as $shipping_key => $line ) {
	$ex_vat       = intval( $line['total'] );
	$vat          = intval( $line['total_tax'] );
	$total        = apply_currency( $ex_vat + $vat );
	$total_ex_vat = apply_currency( $ex_vat );
	$total_vat    = apply_currency( $vat );

	$shipping_vat_rate = 0;
	// Check if taxes are set for shipping line
	if ( isset( $line['taxes'] ) &&
		array_key_exists( 'total', $line['taxes'] ) &&
		! empty( $line['taxes']['total'] ) ) {

		// Check to find tax rate, set if found
		$tax = $all_tax_rates[ array_key_first( $line['taxes']['total'] ) ];
		if ( ! empty( $tax ) ) {
			$shipping_vat_rate = intval( $tax['rate'] );
		}
	}

	array_push(
		$wasa_cart_items,
		array(
			'product_id'           => '-',
			'product_name'         => $line['name'],
			'price_ex_vat'         => $total_ex_vat,
			'price_incl_vat'       => $total,
			'quantity'             => 1,
			'vat_percentage'       => $shipping_vat_rate,
			'vat_amount'           => $total_vat,
			'total_price_incl_vat' => $total,
			'total_price_ex_vat'   => $total_ex_vat,
			'total_vat'            => $total_vat,
		)
	);
}

// Create payload from collected data
$payload = array(
	'payment_types'             => 'invoice',
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
	'shipping_cost_ex_vat'      => apply_currency( $shipping_ex_vat ),
	'request_domain'            => get_site_url(),
	'confirmation_callback_url' => $order->get_checkout_order_received_url(),
	'ping_url'                  => get_rest_url( null, 'wasa-kredit-checkout/v1/update_order_status' ),
	'total_price_incl_vat'      => apply_currency( $order_data['total'] ),
	'total_price_ex_vat'        => apply_currency( number_format( ( $order_data['total'] - $order_data['total_tax'] ), 2, '.', '' ) ),
	'total_vat'                 => apply_currency( number_format( $order_data['total_tax'], 2, '.', '' ) ),
);
// Get answer from API
$response = $client->create_invoice_checkout( $payload );

// Logging.
$log      = Wasa_Kredit_Logger::format_log( '', 'POST', 'create_invoice_checkout', $payload, '', stripslashes_deep( (array) $response ), $response->statusCode ); // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
$level = 'info';
if ( $response->statusCode < 200 || $response->statusCode > 299 ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
	$level = 'error';
}
Wasa_Kredit_Logger::log( $log, $level, 'checkout' );

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

                if (201 === $response->statusCode) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
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
                        echo '<li><strong>Status Code:</strong> ' . esc_html($response->statusCode) . '</li>'; // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
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
		$cancel_url = wc_get_checkout_url();
		?>
		var options = {
			onComplete: function (orderReferences) {
                window.location.href = '<?php echo $order->get_checkout_order_received_url();  // @codingStandardsIgnoreLine - Proceed Url with Query parameters ?>';
			},
			onCancel: function () {
				let checkoutUrl = '<?php echo esc_url( $cancel_url ); ?>';
				window.location.href = checkoutUrl;
			}
		};
		window.wasaCheckout.init(options);
	</script>

<?php
get_footer();
