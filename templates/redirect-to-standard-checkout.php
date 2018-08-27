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
$current_route = end( array_filter( explode( '/', get_permalink() ) ) );

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

    $payment_methods_response = $client->get_payment_methods( $cart_total, 'SEK' );
}
?>

<?php if ( isset( $payment_methods_response ) && 200 === $payment_methods_response->statusCode ) {  ?>
<div class="wasa-kredit-redirect-widget-container">
    <span class="wasa-kredit-redirect-widget-title"><strong><?php _e('Financing', 'wasa-kredit-checkout'); ?></strong></span>
    <ul class="wasa-kredit-redirect-widget-monthly-cost-list">
        <?php 
            foreach ( $payment_methods_response->data['payment_methods'][0]['options']['contract_lengths'] as $key => $value ) { 
                echo "<li>"
                     .wc_price( $value['monthly_cost']['amount'], array( 'decimals' => 0 ) ) . __( '/month', 'wasa-kredit-checkout' )
                     .sprintf( __( ' for %s months.', 'wasa-kredit-checkout' ), $value['contract_length'] )
                     ."</li>";
             } ?>
    </ul>
    <a href="<?php echo $redirect_url ?>" class="wasa-kredit-redirect-widget-button">
        <span class="wasa-kredit-redirect-widget-button-title"><strong><?php _e( 'Change payment method', 'wasa-kredit-checkout' ); ?></strong></span></br>
        <span class="wasa-kredit-redirect-widget-button-description"><?php _e( 'Finance with Wasa Kredit', 'wasa-kredit-checkout' ); ?></span>
    </a>
    <div class="wasa-kredit-redirect-widget-clear"></div>
</div>
<?php } ?>