<?php
function wasa_config($key = '')
{
    $wasa_configuration = [
        'base_url' => 'https://b2b.services.wasakredit.se',
        'access_token_url' => 'https://b2b.services.wasakredit.se/auth/connect/token',
        'version' => 'php-2.4',
        'plugin' => 'woo-1.2.2.1'
    ];

    return isset($wasa_configuration[$key]) ? $wasa_configuration[$key] : null;
}
