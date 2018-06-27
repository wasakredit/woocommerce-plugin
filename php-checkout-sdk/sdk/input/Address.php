<?php

namespace Sdk\Input;

//require_once dirname(__FILE__).'/Payload.php';

class Address extends Payload
{

    private $_payload;

    public function __construct(
        $companyName = null,
        $streetAddress = null,
        $postalCode = null,
        $city = null,
        $country = null
    ) {
        $this->_payload['product_id']   = $companyName;
        $this->_payload['product_name'] = $streetAddress;
        $this->_payload['price_ex_vat'] = $postalCode;
        $this->_payload['quantity']     = $city;
        $this->_payload['quantity']     = $country;
    }
}
