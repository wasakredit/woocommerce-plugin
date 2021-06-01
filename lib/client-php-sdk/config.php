<?php
function wasa_config($key = '')
{
    $wasa_configuration = [
        'base_url' => getEnvOrDefault('WASA_URI', 'https://b2b.services.wasakredit.se'),
        'access_token_url' => getEnvOrDefault('WASA_AUTH_URI', 'https://b2b.services.wasakredit.se/auth/connect/token'),
        'test_base_url' => getEnvOrDefault('WASA_TEST_URI', 'https://api.inttest-b2b.wasakredit.se'),
        'test_access_token_url' => getEnvOrDefault('WASA_TEST_AUTH_URI', 'https://auth.inttest-b2b.wasakredit.se/connect/token'),
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
