<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class SyncController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramSync $sync
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

        return view('instagram.oauth');
    }

    public function index(Request $request)
    {
        // if no access_token from IG go to oauth otherwise render sync process

        $sync = $this->sync;
        $sync->init();

        if (!$sync->hasAccessToken()) {
            return redirect($sync->getAccessTokenUrl());
        }

        $syncUrl = route('instagram.sync.load');
        $syncData = $sync->getSyncData();

        // invalid token after :getSyncData()
        if ($sync->isTokenError()) {
            $sync->clearAccessTokenUrl();
            return redirect($sync->getAccessTokenUrl());
        }

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

        // @fixed
        $advanced = config('instagram.sync_advanced');
        $intervalDefault = config('instagram.sync_interval_default');

        $view = 'instagram.sync.index';
        return view($view,  compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'syncUrl', 'syncData', 'advanced', 'intervalDefault'));
    }

    public function load(Request $request)
    {
        $url = urldecode($request->input('url'));
        $data = $this->sync->getSyncData($url);
        return response()->json($data);
    }

    public function import(Request $request)
    {
        $items = $request->input('items');

        // @see TCG\Voyager\Http\Controllers\VoyagerBreadController::store()

        $slug = 'instagram-media';
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        $media = array();
        foreach ($items as $item) {
            // $newRequest = clone($request);
            $newRequest = new Request();

            $data = $this->sync->createInput($newRequest, $item, new $dataType->model_name());
            $data = $this->insertUpdateData($newRequest, $slug, $dataType->addRows, $data);
            $media[] = $data->toArray();
        }

        return response()->json($media);
    }

    public function sync(Request $request)
    {
        $interval = $request->input('interval');
        $url = (string) $request->input('url');

        // check times
        $format = 'Y-m-d H:i:s';
        $today = date($format);
        $end = date('Y-m-d 23:59:59', strtotime($today));
        $start = date('Y-m-d 00:00:00', strtotime($end . ' - ' . $interval));
        
        // sync process
        $sync = $this->sync;
        $sync->init();

        if (!$sync->hasAccessToken()) {
            return redirect($sync->getAccessTokenUrl());
        }

        $syncUrl = route('instagram.sync.load');
        $syncData = $sync->getSyncData($url, config('instagram.sync_chunk_size'));

        // invalid token after :getSyncData()
        if ($sync->isTokenError()) {
            $sync->clearAccessTokenUrl();
            return redirect($sync->getAccessTokenUrl());
        }

        if ($syncData['data']) {
            $allowDuplicated = config('instagram.allow_duplicates');

            $syncData['added'] = array();
            $items = array();

            $targetDate = strtotime($start);
            foreach ($syncData['data'] as $data) {
                // filter media type
                if ($data['type'] != 'image') {
                    continue;
                }

                // DO NOT duplicated import
                if (!$allowDuplicated && ($data['exists'])) {
                    continue;
                }

                $dataDate = date($format, $data['created_time']);

                if (strtotime($dataDate) >= $targetDate) {
                    $syncData['added'][] = $data;
                    $items[] = array('data' => $data);
                }
            }

            if ($items) {
                unset($syncData['data']);
                $input = array('items' => $items);
                $request->replace($input);
                $this->import($request);
            }
        }

        return response()->json($syncData);
    }
   
}