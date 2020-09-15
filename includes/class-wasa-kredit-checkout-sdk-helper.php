<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_SdkHelper
{
    public static function CreateClient() {
        $settings = get_option( 'wasa_kredit_settings' );

        return Sdk\ClientFactory::CreateClient(
            $settings['partner_id'],
            $settings['client_secret'],
            'yes' === $settings['test_mode']
        );

    }

}