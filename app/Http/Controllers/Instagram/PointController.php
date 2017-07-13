<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class PointController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramPoint $point
     */
    private $point;

    /**
     * @var \TCG\Voyager\Facades\Voyager
     */
    private $voyager;

    public function __construct(\App\InstagramPoint $point)
    {
        $this->point = $point;
    }

    public function points(Request $request, $id)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        #$slug = $this->getSlug($request);
        $slug = 'instagram-media';

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_'.$dataType->name);

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);


        // @todo find all points of media photo
        $points = $dataTypeContent->getPoints($this->voyager);
        $view = 'instagram.point.points';
        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'id', 'points'));
    } 

    public function savePoints(Request $request)
    {
        // @see TCG\Voyager\Http\Controllers\VoyagerBreadController::store()

        $slug = 'instagram-points';
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        $items = $request->input('items');

        $points = array();
        foreach ($items as $key => $item) {
            #$newRequest = clone($request);
            $newRequest = new Request();

            if ($file = $request->file($key)) {
                $item['imageUrl'] = $file;
            }

            $data = $this->point->createInput($newRequest, $item, new $dataType->model_name());
            $data = $this->insertUpdateData($newRequest, $slug, $dataType->addRows, $data);
            $points[] = $data->toArray();
        }

        return response()->json($points);
    }

}