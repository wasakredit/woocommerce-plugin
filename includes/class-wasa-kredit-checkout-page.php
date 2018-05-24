<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class Wasa_Kredit_Checkout_Page
{
    public function __construct()
    {
        //Hooks
        add_filter( 'template_redirect', function( $template ) {
            if ( isset( $_GET['wasa_kredit_checkout'] ) ) {
                include plugin_dir_path( __FILE__ ) . '../templates/checkout-page.php';
                die;
            }
        } );
    }
}
