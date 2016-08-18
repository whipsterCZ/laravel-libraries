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



    /*
    |--------------------------------------------------------------------------
    | POEditor Service | BRAINZ
    |--------------------------------------------------------------------------
    |
    |  If you are using POEditor provide  `project_id`
    |
    |  !!! Important !!!
    |  If you change reserved domains POEditor could replace local translations
    |  @see /resources/lang/
    |
    */
    'poeditor' => [
        'api_token' => 'aaaf2dcd03d9f9c973d5c2f98cc8d362',
        'project_id' => null,  // bAdmin Test 56007
        'reserved_domains' => ['auth','date','messages','passwords','validation','pagination'],
        'add_missing_terms' => env('POEDITOR_ADD_TERMS',false),
    ],


    'google_analytics' => [
        'account' => '',
    ],




];
