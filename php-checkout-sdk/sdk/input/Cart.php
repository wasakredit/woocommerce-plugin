<?php

namespace Sdk\Input;

require_once dirname(__FILE__).'/Payload.php';

class Cart extends Payload
{

    private $_payload;

    public function __construct(
        $productId = null,
        $productName = null,
        $priceExVat = null,
        $quantity = null,
        $vatPercentage = null,
        $vatAmount = null,
        $imageUrl = null
    ) {
        $this->_payload['product_id']     = $productId;
        $this->_payload['product_name']   = $productName;
        $this->_payload['price_ex_vat']   = $priceExVat;
        $this->_payload['quantity']       = $quantity;
        $this->_payload['vat_percentage'] = $vatPercentage;
        $this->_payload['vat_amount']     = $vatAmount;
        $this->_payload['image_url']      = $imageUrl;
    }
}
