<?php

namespace Sdk;

/**
 * Api
 *
 * Executes the requests to the API.
 *
 * @package    Client PHP SDK
 * @author     Jim Skogman <jim.skogman@starrepublic.com>
 */
class Api
{
    public static $POST = "POST";
    public static $GET = "GET";
    public static $PUT = "PUT";
    public static $DELETE = "DELETE";

    private $token_client;
    private $_test_mode;
    private $version;
    private $plugin;

    public function __construct($partnerId, $clientSecret, $testMode)
    {
        $this->token_client = new AccessToken($partnerId, $clientSecret, $testMode);
        $this->_test_mode = $testMode;
        $this->version = wasa_config('version');
        $this->plugin = wasa_config('plugin');
    }

    public function execute($url, $method, $postData)
    {
        if (!$this->token_client->get_token()) {
            return null;
        }

        $headers = array();
        $headers[] = "Authorization: Bearer " . $this->token_client->get_token();
        $headers[] = "Content-Type: application/json";

        if ($this->_test_mode) {
            $headers[] = "x-test-mode: true";
        }

        $headers[] = "x-sdk-version: " . $this->version;
        $headers[] = "x-plugin-version: " . $this->plugin;

        if ($postData != null) {
            $postData = json_encode($postData);
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_TIMEOUT, 2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        switch ($method) {
            case self::$GET:
                break;
            case self::$POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::$PUT:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::$PUT);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            case self::$DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::$DELETE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                break;
            default:
                throw new Exception('Method ' . $method . ' is not recognized.');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 80);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);

        $curl_response   = curl_exec($curl);
        $response_info   = curl_getinfo($curl);
        $curl_error      = curl_error($curl);
        $status_code     = $response_info['http_code'];
        $parsed_response = $this->validate_json($curl_response);
        $response_error  = '';
        $error_message  = '';

        if (is_array($parsed_response) && isset($parsed_response['error_code'])) {
            $response_error = $parsed_response['error_code'];
        }

        if (is_array($parsed_response) && isset($parsed_response['description'])) {
            $response_error = $parsed_response['description'];
        }

        return new Response($status_code, $parsed_response, $response_error, $error_message, $curl_error);
    }

    /**
     * Checks if the input is valid JSON or not
     *
     * @return bool
     */
    private function validate_json($value) // @codingStandardsIgnoreLine
    {
        $parsed_value = json_decode($value, true);
        $validation = (json_last_error() == JSON_ERROR_NONE);
        $return_value = $validation ? $parsed_value : $value;
        return $return_value;
    }
}
