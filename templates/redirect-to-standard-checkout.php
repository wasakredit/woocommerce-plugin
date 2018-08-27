<?php
/**
 * Template Name: Wasa Kredit Redirect To Standard Checkout Template
 *
 * @package Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout
 * @since Wasa_Kredit_Checkout 2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

$settings = get_option( 'wasa_kredit_settings' );

$enable_redirect_widget = $settings['add_redirect_to_standard_checkout_widget'];
$redirect_route = $settings['standard_checkout_page_route'];
$current_route = end(array_filter(explode('/', get_permalink())));

$home_url = get_home_url();
$redirect_url = $home_url."/".$redirect_route;

if ( ! isset( $enable_redirect_widget ) 
    || 'yes' !== $enable_redirect_widget 
    || ! isset( $redirect_route ) 
    || $current_route === $redirect_route
    )
{
    return;
}
// Connect WASA SDK client
$client = new Sdk\Client(
	$settings['partner_id'],
	$settings['client_secret'],
	'yes' === $settings['test_mode'] ? true : false
);

// Get payment methods from API
if ( isset( WC()->cart ) ) {

    $cart_totals = WC()->cart->get_totals();
    $cart_total = $cart_totals['subtotal'] + ( $cart_totals['shipping_total'] - $cart_totals['shipping_tax'] );

    $payment_methods_response = $client->get_payment_methods($cart_total, 'SEK');
}
?>

<?php if ( isset( $payment_methods_response ) && 200 === $payment_methods_response->statusCode ) {  ?>
<div class="wasa-kredit-redirect-widget-container">
    <span class="wasa-kredit-redirect-widget-title"><strong>Finansiering</strong></span>
    <ul class="wasa-kredit-redirect-widget-monthly-cost-list">
        <?php 
            foreach ($payment_methods_response->data['payment_methods'][0]['options']['contract_lengths'] as $key => $value) { 
                echo "<li>".$value['monthly_cost']['amount']." kr/mån i ".$value['contract_length']." månader</li>";
             } ?>
    </ul>
    <a href="<?php echo $redirect_url ?>" class="wasa-kredit-redirect-widget-button">
        <span class="wasa-kredit-redirect-widget-button-title"><strong>Byt betalsätt</strong></span></br>
        <span class="wasa-kredit-redirect-widget-button-description">Finansiera med Wasa Kredit</span>
    </a>
    <div class="wasa-kredit-redirect-widget-clear"></div>
</div>
<?php } ?>