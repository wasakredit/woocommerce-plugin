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

// Collect data about order
$order_id = $_GET['id'];
$order = wc_get_order( $order_id );
$order_data = $order->get_data();
$currency = get_woocommerce_currency();
$shipping_cost = $order_data['shipping_total'];
$shipping_tax = $order_data['shipping_tax'];
$cart_items = WC()->cart->get_cart();
$wasa_cart_items = array();

foreach ($cart_items as $cart_item_key => $cart_item) {
  $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
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
    'price_ex_vat' => array('amount' => $price_ex_vat, 'currency' => $currency),
    'quantity' => $quantity,
    'vat_percentage' => $vat_percentage,
    'vat_amount' => array('amount' => $price_vat, 'currency' => $currency),
  );
}

// Create payload from collected data
$payload = array(
    'order_references' => array(
        array('key' => 'order_id', 'value' => $order_id)
    ),
    'cart_items' => $wasa_cart_items,
    'shipping_cost_ex_vat' => array('amount' => $shipping_ex_vat, 'currency' => $currency),
    'request_domain' => 'https://www.wasakredit.se/',
    'confirmation_callback_url' => 'https://www.wasakredit.se/payment-callback/',
    'ping_url' => 'https://www.wasakredit.se/ping-callback/'
);

// Get answer from API
$response = $client->create_checkout($payload);

get_header();
?>

  <style>
    #wasaIframe {
      min-height: 1000px !important;
    }
    .wasa-logo {
      width: 500px;
      max-width: 100%;
    }
    .entry-title {
      display: none;
    }
    .woocommerce-breadcrumb {
      margin-bottom: 0;
    }
  </style>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

    <img class="wasa-logo" src="<?echo plugin_dir_url( __FILE__ ) . '../public/img/lf-wasa-kredit-logo_left_rgb.png'?>" alt="Wasa Kredit Logotype" />
  <?php if (have_posts()): get_template_part('loop'); endif; ?>

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
        <?php foreach ($cart_items as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
              $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            ?>
            <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

              <td class="product-thumbnail"><?php $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);  if (!$product_permalink) { echo $thumbnail; } else { printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); } ?></td>

              <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>"><?php if (!$product_permalink) { echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;'; } else { echo apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key); }  // Meta data. echo wc_get_formatted_cart_item_data($cart_item);  // Backorder notification. if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) { echo '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>'; } ?></td>

              <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
              </td>

              <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>"><?php $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);  echo $product_quantity; ?></td>

              <td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
              </td>
            </tr>
            <?php } } ?>
      </tbody>
    </table>
    
      <div class="wasa-checkout">
        <?php echo $response->data; ?>
      </div>
		</main>
	</div>

  <script>
    window.wasaCheckout.init();
  </script>

<?php
get_footer();
