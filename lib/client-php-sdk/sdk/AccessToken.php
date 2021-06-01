<?php
/**
 * AccessToken
 *
 * Contains the business logic for requesting
 * and retrieving an access token from the API.
 *
 * @package    Client PHP SDK
 * @author     Jim Skogman <jim.skogman@starrepublic.com>
 */

namespace Sdk;

use \DateTime;
use \DateTimeZone;
use \DateInterval;

/*
if ( ! isset( $_SESSION ) ) {
	session_start();
}
*/


class AccessToken {

	private $_token_url;

	private $client_id;
	private $client_secret;

	public function __construct( $client_id, $client_secret, $auth_url ) {
		$this->_token_url    = $auth_url;
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;

		$this->wasa_kredit_access_token = ! empty( get_transient( 'wasa_kredit_access_token' ) ) ? json_decode( get_transient( 'wasa_kredit_access_token' ), true ) : array();
	}

    private function has_expired() { // @codingStandardsIgnoreLine
		// return ! empty( $_SESSION['wasa_kredit_access_token'][ $this->client_id ]['token_expiry'] ) ? $this->get_date_now_utc() >= $_SESSION['wasa_kredit_access_token'][ $this->client_id ]['token_expiry'] : true;
		return ! empty( $this->wasa_kredit_access_token[ $this->client_id ]['token_expiry'] ) ? $this->get_date_now_utc() >= DateTime::__set_state( $this->wasa_kredit_access_token[ $this->client_id ]['token_expiry'] ) : true;
	}

    private function get_date_now_utc() { // @codingStandardsIgnoreLine
		return new DateTime( 'now', new DateTimeZone( 'UTC' ) );
	}

    private function get_expires_at($seconds) { // @codingStandardsIgnoreLine
		$expiresAt = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$expiresAt->add( new DateInterval( 'PT' . $seconds . 'S' ) );
		return $expiresAt;
	}

    private function constructPOSTFields() { // @codingStandardsIgnoreLine
		$encodedID     = urlencode( $this->client_id );
		$encodedSecret = urlencode( $this->client_secret );
		$fields        = "client_id=$encodedID&client_secret=$encodedSecret&grant_type=client_credentials";
		return $fields;
	}

    public function get_token() { // @codingStandardsIgnoreLine
		/*
		if ( ! empty( $_SESSION['wasa_kredit_access_token'][ $this->client_id ]['access_token'] ) && ! empty( $_SESSION['wasa_kredit_access_token'][ $this->client_id ]['token_expiry'] ) && ! $this->has_expired() ) {
			return $_SESSION['wasa_kredit_access_token'][ $this->client_id ]['access_token'];
		}
		*/

		if ( ! empty( $this->wasa_kredit_access_token[ $this->client_id ]['access_token'] ) && ! empty( $this->wasa_kredit_access_token[ $this->client_id ]['token_expiry'] ) && ! $this->has_expired() ) {
			return $this->wasa_kredit_access_token[ $this->client_id ]['access_token'];
		}

		$curl = curl_init();

		$headers   = array();
		$headers[] = 'content-type: application/x-www-form-urlencoded';

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->_token_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 5,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => $this->constructPOSTFields(),
				CURLOPT_HTTPHEADER     => $headers,
			)
		);

		$response = curl_exec( $curl );
		$err      = curl_error( $curl );

		curl_close( $curl );

		if ( $err ) {
			return null;
		}

		if ( ! $response ) {
			return null;
		}

		$decoded_json = json_decode( $response, true );

		if ( ! empty( $decoded_json['error'] ) ) {
			return null;
		}

		$access_token        = ! empty( $decoded_json['access_token'] ) ? $decoded_json['access_token'] : '';
		$access_token_expiry = ! empty( $decoded_json['expires_in'] )
			? $this->get_expires_at( $decoded_json['expires_in'] )
			: $this->get_date_now_utc();

		$wasa_kredit_access_token                     = array();
		$wasa_kredit_access_token[ $this->client_id ] = array(
			'access_token' => $access_token,
			'token_expiry' => $access_token_expiry,
		);

		// $_SESSION['wasa_kredit_access_token'] = $wasa_kredit_access_token;
		$transient_expiry = ! empty( $decoded_json['expires_in'] ) ? $decoded_json['expires_in'] : '3600';
		set_transient( 'wasa_kredit_access_token', wp_json_encode( $wasa_kredit_access_token ), $transient_expiry );
		// return $wasa_kredit_access_token;
		return $access_token;
	}
}
