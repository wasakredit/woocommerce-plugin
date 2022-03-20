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
				<?php wasa_kredit_get_invoice_snippet( $order_id ); ?>
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
