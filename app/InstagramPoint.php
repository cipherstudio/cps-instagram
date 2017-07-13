<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class InstagramPoint extends Model
{

    public function createInput(Request $request, array $item, $data)
    {
        $now = date('F jS, Y h:i A');

        $input = array(
            'name' => '',
            'number' => $item['number'],
            'pos_x' => $item['posX'],
            'pos_y' => $item['posY'],
            'size' => 28,
            'url' => $item['url'],
            'media_id' => $item['mediaId'],
            'image_url' => isset($item['imageUrl']) ? $item['imageUrl'] : '',
            'created_at' => $now,
            'updated_at' => $now
        );

        // @fixed timestamp field
        foreach ( array('created_at', 'updated_at', 'deleted_at') as $key) {
            if (isset($input[$key]) && !empty($input[$key])) {
                $data->$key = strtotime($input[$key]);
                unset($input[$key]);
            }
        }

        if ($input['image_url']) {
            $request->files->replace(array('image_url' => $input['image_url']));
        }
        
        $request->replace($input);

        return $data;
    }

}