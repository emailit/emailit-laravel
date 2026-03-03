<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emailit API Key
    |--------------------------------------------------------------------------
    |
    | Your Emailit API key. You can find this in your Emailit dashboard
    | at https://app.emailit.com/workspaces/~/settings/api-keys
    |
    */

    'api_key' => env('EMAILIT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Emailit API. You shouldn't need to change this
    | unless you are using a custom or self-hosted instance.
    |
    */

    'api_base' => env('EMAILIT_API_BASE', 'https://api.emailit.com/v2'),

];
