<?php
/**
 * Template Name: Wasa Kredit Checkout Template
 *
 * @package Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout
 * @since Wasa_Kredit_Checkout 1.0
 */

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

if (!$_GET['id'] || empty($_GET['id'])) {
    exit();
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

// Connect WASA SDK client
$client = new Sdk\Client(
    "03efa7fb-e3bf-4699-84d1-927de133dba7",
    "abc123",
    true
);

$settings = get_option('wasa_kredit_settings');

// Collect data about order
$order_id = $_GET['id'];
$order = wc_get_order($order_id);
$order_data = $order->get_data();
$currency = get_woocommerce_currency();
$shipping_cost = $order_data['shipping_total'];
$shipping_tax = $order_data['shipping_tax'];
$cart_items = WC()->cart->get_cart();
$wasa_cart_items = array();

foreach ($cart_items as $cart_item_key => $cart_item) {
    $product = apply_filters(
        'woocommerce_cart_item_product',
        $cart_item['data'],
        $cart_item,
        $cart_item_key
    );
    $id = $cart_item['product_id'];
    $name = $product->get_name();
    $price_inc_vat = $product->get_price_including_tax();
    $price_ex_vat = $product->get_price_excluding_tax();
    $vat_percentage = ($price_ex_vat - $price_inc_vat) * 100;
    $price_vat = $price_inc_vat - $price_ex_vat;
    $shipping_ex_vat = $shipping_cost - $shipping_tax;
    $quantity = $cart_item['quantity'];

    $wasa_cart_items[] = array(
        'product_id' => $id,
        'product_name' => $name,
        'price_ex_vat' => array(
            'amount' => $price_ex_vat,
            'currency' => $currency
        ),
        'quantity' => $quantity,
        'vat_percentage' => $vat_percentage,
        'vat_amount' => array('amount' => $price_vat, 'currency' => $currency)
    );
}

// Create payload from collected data
$payload = array(
    'purchaser_name' => $order->billing_first_name . " " . $order->billing_last_name,
    'purchaser_email' => $order->billing_email,
    'purchaser_phone' => $order->billing_phone,
    'recipient_name' => $order->shipping_first_name . " " . $order->shipping_last_name,
    'recipient_phone' => $order->billing_phone,
    'billing_address' => array(
      'company_name' => $order->billing_company,
      'street_address' => $order->billing_address_1,
      'postal_code' => $order->billing_postcode,
      'city' => $order->billing_city,
      'country' => $order->billing_country
    ),
    'delivery_address' => array(
      'company_name' => $order->shipping_company,
      'street_address' => $order->shipping_address_1,
      'postal_code' => $order->shipping_postcode,
      'city' => $order->shipping_city,
      'country' => $order->shipping_country
    ),
    'order_references' => array(
        array('key' => 'key', 'value' => $order->order_key)
    ),
    'cart_items' => $wasa_cart_items,
    'shipping_cost_ex_vat' => array(
        'amount' => $shipping_ex_vat,
        'currency' => $currency
    ),
    'request_domain' => get_site_url(),
    'confirmation_callback_url' => get_site_url(null, '/checkout/order-received/' . $order_id . '/?key=' . $order->order_key),
    'ping_url' => get_site_url(null, '/wasa-kredit-checkout/order-update/')
);

// Get answer from API
$response = $client->create_checkout($payload);

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

  <?php if (have_posts()): get_template_part('loop'); endif; ?>

  <?php if ($settings['cart_on_checkout'] === "yes"): ?>
  <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
      <thead>
        <tr>
          <th class="product-thumbnail">&nbsp;</th>
          <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
          <th class="product-price"><?php esc_html_e('Price', 'woocommerce'); ?></th>
          <th class="product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
          <th class="product-subtotal"><?php esc_html_e('Total', 'woocommerce'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart_items as $cart_item_key => $cart_item) { $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key); $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key); if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) { $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key); ?>
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
        <?php echo $response->data; ?>
      </div>
		</main>
	</div>

  <script>
    var options = {
      onComplete: function (orderReferences) {
        console.log(orderReferences);
      },
      onCancel: function () {
        var checkoutUrl = '<?php echo get_site_url(null, '/checkout/'); ?>';
        window.location.href = checkoutUrl;
      }
    };

    window.wasaCheckout.init(options);
  </script>

<?php // Meta data. echo wc_get_formatted_cart_item_data($cart_item);  // Backorder notification. if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) { echo '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>'; }
get_footer();
