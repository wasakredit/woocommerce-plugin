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

if (!isset($_SESSION)) {
    session_start();
}

class AccessToken
{
    private $_token_url;
    private $token;
    private $expires_at;

    private $client_id;
    private $client_secret;
    private $test_mode;

    public function __construct($client_id, $client_secret, $test_mode)
    {
        $this->_token_url = wasa_config('access_token_url');
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->test_mode = $test_mode;
    }

    private function has_expired()
    {
        return !empty($_SESSION["token_expiry"])
            ? $this->get_date_now_utc() >= $_SESSION["token_expiry"]
            : true;
    }

    private function get_date_now_utc()
    {
        return new DateTime('now', new DateTimeZone('UTC'));
    }

    private function get_expires_at($seconds)
    {
        $expiresAt = new DateTime('now', new DateTimeZone('UTC'));
        $expiresAt->add(new DateInterval('PT' . $seconds . 'S'));
        return $expiresAt;
    }

    private function constructPOSTFields()
    {
        $encodedID = urlencode($this->client_id);
        $encodedSecret = urlencode($this->client_secret);
        $fields = "client_id=$encodedID&client_secret=$encodedSecret&grant_type=client_credentials";
        return $fields;
    }

    public function get_token()
    {
        if (
            !empty($_SESSION["token"]) &&
            !empty($_SESSION["token_expiry"]) &&
            !$this->has_expired()
        ) {
            return $_SESSION["token"];
        }

        $curl = curl_init();

        $headers = array();
        $headers[] = "content-type: application/x-www-form-urlencoded";

        if ($this->test_mode) {
            $headers[] = "x-test-mode: true";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->_token_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $this->constructPOSTFields(),
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        // Enable if dev mode
        // echo "cURL Error #:" . $err;
        if ($err) {
            return null;
        }
        if (!$response) {
            return null;
        }

        $decoded_json = json_decode($response, true);

        if (!empty($decoded_json['error'])) {
            return null;
        }

        $_SESSION["token"] = !empty($decoded_json['access_token'])
            ? $decoded_json['access_token']
            : "";
        $_SESSION["token_expiry"] = !empty($decoded_json['expires_in'])
            ? $this->get_expires_at($decoded_json['expires_in'])
            : $this->get_date_now_utc();

        return $_SESSION["token"];
    }
}
