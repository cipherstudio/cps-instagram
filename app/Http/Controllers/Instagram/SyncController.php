<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class SyncController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramApi $api
     */
    private $api;

    public function __construct(\App\InstagramApi $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        // @todo if no access_token from IG go to oauth otherwise render sync process

        $api = $this->api;

        $accessToken = @$_COOKIE[\App\InstagramApi::COOKIE_ACCESS_TOKEN_KEY] ?: '';
        if (!$accessToken) {
            return redirect($api->getAccessTokenUrl());
        }

        $api->setAccessToken($accessToken);

        $items = $api->getItems();

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
        return view($view,  compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'items'));
    }
}