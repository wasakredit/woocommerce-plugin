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

// Collect data about order.
$order_id = wc_get_order_id_by_order_key( $order_key );
$order    = wc_get_order( $order_id );

if ( ! $order ) {
	exit();
}

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
			<?php wasa_kredit_get_leasing_snippet( $order_id ); ?>
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

			var url = '<?php echo esc_url( $confirm_url ); ?>' + '=' + wasaKreditOrderId;

			jQuery.ajax(url);
			window.location.href = '<?php echo esc_url( $order->get_checkout_order_received_url() ); ?>';
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
