<?php

namespace Sdk\Input;

require_once dirname(__FILE__).'/Payload.php';

// Used for Price, VAT, Shipping
class Price extends Payload
{

    private $_payload;

    public function __construct($amount = null, $currency = null)
    {
        $this->_payload['amount'] = $amount;
        $this->_payload['currency'] = $currency;
    }
}
