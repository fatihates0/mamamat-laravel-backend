<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vatan_sms' => [
        'url' => env('VATAN_SMS_URL', 'http://panel.vatansms.com/panel/smsgonder1Npost.php'),
        'customer_no' => env('VATAN_SMS_CUSTOMER_NO', '42215'),
        'username' => env('VATAN_SMS_USERNAME', '905078721533'),
        'password' => env('VATAN_SMS_PASSWORD', '337dAz15'),
        'originator' => env('VATAN_SMS_ORIGINATOR', '8505902979'),
    ],

];
