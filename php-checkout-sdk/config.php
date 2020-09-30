<?php
function wasa_config($key = '')
{
    $wasa_configuration = [
        'base_url' => getEnvOrDefault('WASA_URI', 'http://st1-b2b.services.wasakredit.se'),
        //'base_url' => getEnvOrDefault('WASA_URI', 'https://b2b.services.wasakredit.se'),
        'access_token_url' => getEnvOrDefault('WASA_AUTH_URI', 'http://st1-b2b.services.wasakredit.se/auth/connect/token'),
        //'access_token_url' => getEnvOrDefault('WASA_AUTH_URI', 'https://b2b.services.wasakredit.se/auth/connect/token'),
        'test_base_url' => getEnvOrDefault('WASA_TEST_URI', 'http://st1-b2b.services.wasakredit.se'),
        //'test_base_url' => getEnvOrDefault('WASA_TEST_URI', 'http://localhost:5004'),
        'test_access_token_url' => getEnvOrDefault('WASA_TEST_AUTH_URI', 'http://st1-b2b.services.wasakredit.se/auth/connect/token'),
        // 'test_access_token_url' => getEnvOrDefault('WASA_TEST_AUTH_URI', 'http://wkcloud-front-lb-1268786071.eu-central-1.elb.amazonaws.com:83/'),
        'version' => 'php-2.5',
        'plugin' => null
    ];  

    return isset($wasa_configuration[$key]) ? $wasa_configuration[$key] : null;
}

function getEnvOrDefault($key, $default = null)
{
    $var = getenv($key);
    return $var == false ? $default : $var;
}