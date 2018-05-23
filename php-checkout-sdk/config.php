<?php
function wasa_config($key = '')
{
    $wasa_configuration = [
        'base_url' => 'http://wkcloud-front-lb-1268786071.eu-central-1.elb.amazonaws.com:82',
        'access_token_url' => 'http://wkcloud-front-lb-1268786071.eu-central-1.elb.amazonaws.com:83/connect/token',
        'version' => 'php-2.2',
        'plugin' => 'woo-1.0'
    ];

    return isset($wasa_configuration[$key]) ? $wasa_configuration[$key] : null;
}
