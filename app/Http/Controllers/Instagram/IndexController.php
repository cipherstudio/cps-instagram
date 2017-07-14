<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class IndexController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramMedia $sync
     */
    private $media;

    public function __construct(\App\InstagramMedia $media)
    {
        $this->media = $media;
        $this->media->init();
    }

    public function index(Request $request)
    {
        $syncUrl = route('instagram.index.load');
        $syncData = $this->media->getSyncData();

        return view('instagram.index.index', compact('syncUrl', 'syncData'));
    }

    public function load(Request $request)
    {
        $url = urldecode($request->input('url'));
        $data = $this->media->getSyncData($url);
        return response()->json($data);
    }

}
