<?php
namespace Sdk;

class ClientFactory {
    public static function CreateClient($clientId, $clientSecret, $testMode = true) {
        if($testMode != true) {
            return new Client($clientId, $clientSecret, 'base_url', 'access_token_url', false);
        }
        else {
            return new Client($clientId, $clientSecret, 'test_base_url', 'test_access_token_url', true);
        }
    }
}

/**
 * Client
 *
 * The main client that orchestrates requests to the API.
 * It also delegates other responsibilities, such as fetching an access token
 * to other classes.
 *
 * @package    Client PHP SDK
 */
class Client
{
    private $base_url;
    private $api_client;
    private $codes = array(
        '100' => 'Continue',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '503' => 'Service Unavailable'
    );

    public function __construct($clientId, $clientSecret, $base_url_config, $access_token_url_config, $testMode)
    {
        $this->base_url = wasa_config($base_url_config);
        $token_client = new AccessToken($clientId, $clientSecret, $access_token_url_config, $testMode);
        $this->api_client = new Api($token_client, $testMode);
    }

    /**
     * @deprecated
     */
    public function calculate_leasing_cost($calculateLeasingBody) // @codingStandardsIgnoreLine
    {
        // Method calculate_leasing_cost is deprecated, use calculate_monthly_cost instead.
        return $this->api_client->execute($this->base_url . "/v1/leasing", "POST", $calculateLeasingBody);
    }

    public function calculate_monthly_cost($calculateMonthlyCostBody) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/monthly-cost", "POST", $calculateMonthlyCostBody);
    }

    /**
     * @deprecated
     */
    public function calculate_total_leasing_cost($calculateTotalLeasingCostBody) // @codingStandardsIgnoreLine
    {
        // Method calculate_total_leasing_cost is deprecated.
        return $this->api_client->execute($this->base_url . "/v1/leasing/total-cost", "POST", $calculateTotalLeasingCostBody);
    }

    public function create_checkout($createCheckoutBody) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v2/checkouts", "POST", $createCheckoutBody);
    }

    public function create_invoice_checkout($createCheckoutBody) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/invoiceCheckouts", "POST", $createCheckoutBody);
    }

    /**
     * @deprecated
     */
    public function validate_allowed_leasing_amount($amount) // @codingStandardsIgnoreLine
    {
        // Method validate_allowed_leasing_amount is deprecated, use validate_financed_amount instead.
        return $this->api_client->execute($this->base_url . "/v1/leasing/validate-amount?amount=" . $amount, "GET", null);
    }

    public function validate_financed_amount($amount) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/validate-financed-amount?amount=" . $amount, "GET", null);
    }

    /**
    * @deprecated
    */
    public function create_product_widget($productPrice) // @codingStandardsIgnoreLine
    {
        // Method create_product_widget is deprecated, use get_monthly_cost_widget instead.
        return $this->api_client->execute($this->base_url . "/v1/checkouts/widget", "POST", $productPrice);
    }

    public function get_monthly_cost_widget($amount) //@codingStandardsIgnoreLine
    {
      return $this->api_client->execute($this->base_url . "/v2/widgets/monthly-cost?amount=".$amount."&currency=SEK", "GET", null);
    }

    public function add_order_reference($orderId, $orderReferences) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/orders/" . $orderId . "/order-references", "POST", $orderReferences);
    }

    public function get_order($orderId) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/orders/" . $orderId, "GET", null);
    }

    public function get_order_status($orderId) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/orders/" . $orderId . "/status", "GET", null);
    }

    public function update_order_status($orderId, $orderStatus) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/orders/" . $orderId . "/status/" . $orderStatus, "PUT", null);
    }

    public function get_payment_methods($amount, $currency) // @codingStandardsIgnoreLine
    {
        return $this->api_client->execute($this->base_url . "/v1/payment-methods?total_amount=" . $amount . "&currency=" . $currency, "GET", null);
    }
}
