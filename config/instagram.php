<?php


// @todo by app env such as development and/or production

return [

    'oauth' => [
        'url' => 'https://instagram.com/oauth/authorize',
        'app' => [
            'client_id' =>     '30f128278b88472da3c0602c615f9079',
            'redirect_uri' =>  'http://instagram.cpsclothing.com/instagram/oauth',
            'response_type' => 'token'
        ]
    ],

    'api' => [
        'url' => 'https://api.instagram.com/v1',

        // @see https://instagram.com/developer/endpoints/users/#get_users
        'endpoints' => [
            'get_current_account' => 'users/self',
            'files/list_folder' => 'users/self/feed'
        ]
    ],

    'page_size' => 5

];

