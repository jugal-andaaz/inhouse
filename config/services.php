<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'stripe' => [
        'model' => Vanguard\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'mailtrap' => [
        'default_inbox' => '58948',
        'secret' => env('MAILTRAP_SECRET'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_CALLBACK_URI'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_CALLBACK_URI'),
        'oauth' => 2,
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CALLBACK_URI'),
    ],

    'skeepers' => [
        'client_id'     => env('SKEEPERS_CLIENT_ID'),
        'client_secret' => env('SKEEPERS_CLIENT_SECRET'),
    ],

    'fedex' => [
        'client_id'     => env('FEDEX_CLIENT_ID'),
        'client_secret' => env('FEDEX_CLIENT_SECRET'),
        'base_url'      => env('FEDEX_BASE_URL'),
    ],

    'fedexftb' => [
        'client_id'      => env('FEDEX_CLIENT_IDS'),
        'client_secret'  => env('FEDEX_CLIENT_SECRETS'),
        'base_url'       => env('FEDEX_BASE_URLS'),
        'account_number' => env('FEDEX_ACCOUNT_NUMBER'),
        'document_url'   => env('FEDEX_DOCUMENT_URL'),
        'shipper' => [
            'name'        => env('FEDEX_SHIPPER_NAME', 'Andaaz Fashion'),
            'company'     => env('FEDEX_SHIPPER_COMPANY', 'SV Global Designs Pvt. Ltd'),
            'phone'       => env('FEDEX_SHIPPER_PHONE'),
            'email'       => env('FEDEX_SHIPPER_EMAIL'),
            'street'      => env('FEDEX_SHIPPER_STREET'),
            'city'        => env('FEDEX_SHIPPER_CITY'),
            'state'       => env('FEDEX_SHIPPER_STATE'),
            'postal'      => env('FEDEX_SHIPPER_POSTAL'),
            'country'     => env('FEDEX_SHIPPER_COUNTRY', 'IN'),
            'gstin'       => env('FEDEX_SHIPPER_GSTIN'),
        ],
    ],

    'authy' => [
        'key' => env('AUTHY_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
];
