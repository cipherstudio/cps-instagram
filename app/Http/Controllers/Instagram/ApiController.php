<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;

class ApiController extends \App\Http\Controllers\Controller
{
    public function oauth(Request $request)
    {
        // If you want to get the value after the hash mark or anchor as shown in a user's browser: 
        // This isn't possible with "standard" HTTP as this value is never sent to the server
        //
        // @see https://stackoverflow.com/questions/2317508/get-fragment-value-after-hash-from-a-url-in-php

        #$accessToken = $request->cookie('instagram_access_token');
        // $accessToken = $_COOKIE['instagram_access_token'];
        // zf_dump($accessToken);

        return view('instagram.oauth');
    }
}
