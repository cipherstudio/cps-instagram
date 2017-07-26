<?php


// @todo by app env such as development and/or production

return [

    'oauth' => [
        'url' => 'https://instagram.com/oauth/authorize',
        'scope' => 'public_content',
        'app' => [
            'client_id' =>     '30f128278b88472da3c0602c615f9079',
            'redirect_uri' =>  config('app.url') . '/instagram/oauth',
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


    // account
    'allow_fixed_user' => true,
    'user_id' => '209201990', // cps: 209201990, rcoco66: 11692348
    
    'publisher_route_index' => 'voyager.instagram-media.index',
    // 'publisher_route_index' => 'instagram.sync.index',

    'force_oauth' => true,

    'sort_by' => 'sort',
    'sort_dir' => 'DESC',

    // pagination
    'page_size' => 12, // frontend
    'sync_page_size' => 18, // backend
    'sync_chunk_size' => 1, // used for auto sync and progress for end user

    // sync
    'allow_duplicates' => false,

    'sync_advanced' => true,
    'sync_interval_default' => '1 week'
];

