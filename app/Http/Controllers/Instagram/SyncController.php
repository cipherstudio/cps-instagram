<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class SyncController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramApi $sync
     */
    private $sync;

    public function __construct(\App\InstagramSync $sync)
    {
        $this->sync = $sync;
    }

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

    public function index(Request $request)
    {
        // @todo if no access_token from IG go to oauth otherwise render sync process

        $sync = $this->sync;
        $sync->init();

        if (!$sync->hasAccessToken()) {
            return redirect($sync->getAccessTokenUrl());
        }

        $syncData = $sync->getSyncData();

        // GET THE SLUG, ex. 'posts', 'pages', etc.
        #$slug = $this->getSlug($request);
        $slug = 'instagram-media';

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_'.$dataType->name);

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $dataTypeContent = array();

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            /*
            $relationships = $this->getRelationships($dataType);

            if ($model->timestamps) {
                $dataTypeContent = call_user_func([$model->with($relationships)->latest(), $getter]);
            } else {
                $dataTypeContent = call_user_func([
                    $model->with($relationships)->orderBy($model->getKeyName(), 'DESC'),
                    $getter,
                ]);
            }

            //Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
            //*/
        } else {
            // If Model doesn't exist, get data from table name
            #$dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        $view = 'instagram.sync.index';
        return view($view,  compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'syncData'));
    }

    public function load(Request $request)
    {
        $url = urldecode($request->input('url'));
        $data = $this->sync->getSyncData($url);
        return response()->json($data);
    }
}