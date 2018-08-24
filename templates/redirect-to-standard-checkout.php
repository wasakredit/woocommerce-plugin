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
<div style="border:1px solid #efefef;padding:15px;margin-bottom:40px;background-color:#fbfbfb">
    <span style="display:block"><strong>Finansiering</strong></span>
    <a href="/?page_id=6" style="display:inline-block;float:right;color:#fff;background-color:#ad1015;padding:10px 20px">
        <span><strong>Byt betalsätt</strong></span></br>
        <span>Finansiera med Wasa Kredit</span>
    </a>
    <ul style="list-style:none;margin:0px">
        <?php 
            foreach ($payment_methods_response->data['payment_methods'][0]['options']['contract_lengths'] as $key => $value) { 
                echo "<li>".$value['monthly_cost']['amount']." kr/mån i ".$value['contract_length']." månader</li>";
             } ?>
    </ul>
    <div style="clear:both"></div>
</div>
<?php } ?>