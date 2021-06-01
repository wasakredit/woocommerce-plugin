<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/lib/client-php-sdk/Wasa.php';

class Wasa_Kredit_Checkout_SdkHelper {

	public static function CreateClient() {
		$settings = get_option( 'wasa_kredit_settings' );

		return Sdk\ClientFactory::CreateClient(
			'yes' === $settings['test_mode'] ? $settings['test_partner_id'] : $settings['partner_id'],
			'yes' === $settings['test_mode'] ? $settings['test_client_secret'] : $settings['client_secret'],
			'yes' === $settings['test_mode'] ? true : false
		);

	}

}
