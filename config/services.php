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
        'domain' => '',
        'secret' => '',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => \App\Model\User::class,
        'key' => '',
        'secret' => '',
    ],

    'pusher' => [
        'key' => env('PUSHER_KEY'),
        'secret' => env('PUSHER_SECRET'),
        'id' => env('PUSHER_ID'),
    ],

    'gitlab' => [
        'url' => env('GITLAB_URL'),
        'client_id' => env('GITLAB_APP_ID'),
        'client_secret' => env('GITLAB_SECRET'),
        'redirect' => env('APP_URL') . '/internal/auth/callback/gitlab',
    ],

    'github' => [
        'client_id' => env('GITHUB_APP_ID'),
        'client_secret' => env('GITHUB_SECRET'),
        'redirect' => env('APP_URL') . '/internal/auth/callback/github',
    ],

    'bitbucket' => [
        'client_id' =>  env('BITBUCKET_APP_ID'),
        'client_secret' => env('BITBUCKET_SECRET'),
        'redirect' => env('APP_URL') . '/internal/auth/callback/bitbucket',
    ],
];
