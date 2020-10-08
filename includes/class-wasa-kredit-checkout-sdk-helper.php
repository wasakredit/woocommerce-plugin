<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

require_once plugin_dir_path( __FILE__ ) . '../vendor/wasa/client-php-sdk/Wasa.php';

class Wasa_Kredit_Checkout_SdkHelper
{
    public static function CreateClient() {
        $settings = get_option( 'wasa_kredit_settings' );

        return Sdk\ClientFactory::CreateClient(
            'yes' === $settings['test_mode'] ? $settings['test_partner_id'] : $settings['partner_id'],
            'yes' === $settings['test_mode'] ? $settings['test_client_secret'] : $settings['client_secret']
            
        );

    }

}