<?php

namespace Sdk;

/**
 * Response
 *
 * A blueprint of the response format for all requests.
 *
 * @package    Client PHP SDK
 * @author     Jim Skogman <jim.skogman@starrepublic.com>
 */
class Response
{

    private $statusCode = '';
    private $data = '';
    private $error = '';
    private $errorMessage = '';
    private $curlError = '';

    public function __construct(
        $statusCode = null,
        $data = null,
        $error = null,
        $errorMessage = null,
        $curlError = null
    ) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->error = $error;
        $this->errorMessage = $errorMessage;
        $this->curlError = $curlError;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }
}
