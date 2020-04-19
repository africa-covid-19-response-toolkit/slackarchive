<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Query Cache Time Out
    |--------------------------------------------------------------------------
    |
    | This option controls the time out of Query Cache in Models, change it
    | to effect globally on all models
    | Becarefully, cache to long will make your app out date, to short will
    | make your app work without cache and crash whole system
    | Values is in Minutes
    |
    */

    'secret_key' => '7e79d196fe0797xxxxxxxxxxx',

    'team_name' => 'Ethiopia COVID-19 Response Team',

    'full_domain' => getenv('SLACK_FULL_DOMAIN'),

    'query_cache' => [
        'timeout_long' => 20,
        'timeout_medium' => 15,
        'timeout_short' => 5,
    ],

    'num_posts_per_page' => 100,

];

?>
