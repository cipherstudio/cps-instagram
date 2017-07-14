<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class InstagramMedia extends Model
{

    public function getPoints()
    {
        $rows = DB::table('instagram_points')->where('media_id', $this->id)->get();

        $points = array();
        foreach ($rows as $row) {
            settype($row, 'array');

            //@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif
            if (strpos($row['image_url'], 'http://') === false && strpos($row['image_url'], 'https://') === false) {
                $row['image_url'] = app('voyager')->image( $row['image_url'] );
            }

            $points[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'number' => $row['number'],
                'posX' => $row['pos_x'],
                'posY' => $row['pos_y'],
                'size' => $row['size'],
                'url' => $row['url'],
                'mediaId' => $row['media_id'],
                'imageUrl' => $row['image_url'],
                'createdAt' => $row['created_at'],
                'updatedAt' => $row['updated_at'],
            );
        }

        return $points;
    }
}