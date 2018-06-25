<?php

use PHPUnit\Framework\TestCase;

use Sdk\AccessToken;
use Sdk\Api;

class AuthenticateTest extends TestCase {

  public function testGetAuthToken() {
    $accessToken = new AccessToken('', '', true);    
    $this->assertEquals($accessToken->get_token(), null);
  }
}
