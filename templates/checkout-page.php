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

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

$settings = get_option( 'wasa_kredit_settings' );

// Connect WASA SDK client
$client = new Sdk\Client(
    $settings['partner_id'],
    $settings['client_secret'],
    $settings['test_mode'] == 'yes' ? true : false
);

// Collect data about order
$order_key  = $_GET['wasa_kredit_checkout'];
$order_id   = wc_get_order_id_by_order_key( $order_key );
$order      = wc_get_order( $order_id );

if ( ! $order ) {
    exit();
}

$order_data       = $order->get_data();
$currency         = get_woocommerce_currency();
$shipping_cost    = $order_data['shipping_total'];
$shipping_tax     = $order_data['shipping_tax'];
$cart_items       = WC()->cart->get_cart();
$wasa_cart_items  = array();

foreach ( $cart_items as $cart_item_key => $cart_item ) {
    $product = apply_filters(
        'woocommerce_cart_item_product',
        $cart_item['data'],
        $cart_item,
        $cart_item_key
    );

    $id               = $cart_item['product_id'];
    $name             = $product->get_name();
    $price_inc_vat    = wc_get_price_including_tax($product);
    $price_ex_vat     = wc_get_price_excluding_tax($product);
    $vat_percentage   = ( $price_inc_vat > 0 ? ( $price_ex_vat / $price_inc_vat ) * 100 : 0 );
    $price_vat        = $price_inc_vat - $price_ex_vat;
    $shipping_ex_vat  = $shipping_cost - $shipping_tax;
    $quantity         = $cart_item['quantity'];

    $wasa_cart_items[] = array(
        'product_id'      => $id,
        'product_name'    => $name,
        'price_ex_vat'    => array(
            'amount'   => $price_ex_vat,
            'currency' => $currency
        ),
        'quantity'        => $quantity,
        'vat_percentage'  => $vat_percentage,
        'vat_amount'      => array( 'amount' => $price_vat, 'currency' => $currency )
    );
}

// Create payload from collected data
$payload = array(
    'purchaser_name' => $order->get_billing_first_name() .
        " " .
        $order->get_billing_last_name(),
    'purchaser_email' => $order->get_billing_email(),
    'purchaser_phone' => $order->get_billing_phone(),
    'recipient_name' => $order->get_shipping_first_name() .
        " " .
        $order->get_shipping_last_name(),
    'recipient_phone' => $order->get_billing_phone(),
    'billing_address' => array(
        'company_name' => $order->get_billing_company(),
        'street_address' => $order->get_billing_address_1(),
        'postal_code' => $order->get_billing_postcode(),
        'city' => $order->get_billing_city(),
        'country' => $order->get_billing_country()
    ),
    'delivery_address' => array(
        'company_name' => $order->get_shipping_company(),
        'street_address' => $order->get_shipping_address_1(),
        'postal_code' => $order->get_shipping_postcode(),
        'city' => $order->get_shipping_city(),
        'country' => $order->get_shipping_country()
    ),
    'order_references' => array(
        array( 'key' => 'key', 'value' => $order->get_order_key() )
    ),
    'cart_items' => $wasa_cart_items,
    'shipping_cost_ex_vat' => array(
        'amount' => $shipping_ex_vat,
        'currency' => $currency
    ),
    'request_domain' => get_site_url(),
    'confirmation_callback_url' => $order->get_checkout_order_received_url(),
    'ping_url' => get_site_url( null, '/wasa-kredit-checkout/order-update/' )
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

  <?php if ( $settings['cart_on_checkout'] === 'yes' ): ?>
  <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
      <thead>
        <tr>
          <th class="product-thumbnail">&nbsp;</th>
          <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
          <th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
          <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
          <th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $cart_items as $cart_item_key => $cart_item ) { $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key); $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key); if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) { $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key); ?>
            <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

              <td class="product-thumbnail"><?php $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key); if (!$product_permalink) { echo $thumbnail; } else { printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); } ?></td>

              <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>"><?php if (!$product_permalink) { echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;'; } else { echo apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key); } ?></td>

              <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
              </td>

              <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>"><?php echo $cart_item['quantity']; ?></td>

              <td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
              </td>
            </tr>
            <?php } } ?>
        </tbody>
      </table>
      <?php endif; ?>

      <div class="wasa-checkout">
        <?php
          if ($response->statusCode == 201) {
            echo $response->data;
          }
          else {
            echo '<p><strong style="color: red">' . __( " Something went wrong while contacting Wasa Kredit API." ) . '</strong></p>';

            if ($settings['test_mode'] == "yes") {
              echo "<hr/>";
              echo "<h4>Request to API</h4>";
              echo "<pre>".json_encode($payload, JSON_PRETTY_PRINT)."</pre>";
              echo "<hr/>";
              echo "<h4>Response from Api</h4>";
              echo "<p>Wasa Kredit checkout is currently set to run in test mode, the plugin will echo out the error response from the Wasa Kredit Checkout API for easier debugging.</p>";
              echo "<ul>";
              echo "<li><strong>Status Code:</strong> ".$response->statusCode."</li>";
              echo "<li><strong>Error:</strong> ".$response->error."</li>";
              echo "</ul>";
              echo "<pre>".json_encode($response->data, JSON_PRETTY_PRINT)."</pre>";
              echo "<hr/>";
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
        var transactionId = orderReferences[1].value;
        var url = '<?php echo get_site_url( null, '/wc-api/wasa-order-payment-complete?key=' . $order->get_order_key() . '&transactionId=' ); ?>' + transactionId;

        jQuery.ajax(url);
        window.location.href = "<?php echo $order->get_checkout_order_received_url() ?>";
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
