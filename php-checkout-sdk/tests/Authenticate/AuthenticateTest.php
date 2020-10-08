<?php

namespace Authenticate;

use PHPUnit\Framework\TestCase;

use Sdk\AccessToken;


class AuthenticateTest extends TestCase {

  public function testGetAuthToken() {
    $accessToken = new AccessToken(getenv('clientId'), getenv('clientSecret'), getenv('test_access_token_url'));
    $this->assertNotNull($accessToken->get_token());
  }

}
