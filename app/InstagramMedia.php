<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class InstagramMedia extends Model
{

    /**
     * @var integer $itemCountPerPage
     */
    protected $itemCountPerPage = 10;

    public function init()
    {
        $config = $this->getConfig();
        $this->setItemCountPerPage($config['page_size']);
    }

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = config('instagram');
        }

        return $this->config;
    }

    public function setItemCountPerPage($itemCountPerPage)
    {
        // @fixed if nlt 20 it doesn't return pagination data
        //        may be sandbox mode
        $this->itemCountPerPage = $itemCountPerPage;
        return $this;
    }

    public function getItemCountPerPage()
    {
        return $this->itemCountPerPage;
    }

    protected function transformPointRow($row)
    {
        settype($row, 'array');
        //@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif
        if (strpos($row['image_url'], 'http://') === false && strpos($row['image_url'], 'https://') === false) {
            $row['image_url'] = app('voyager')->image( $row['image_url'] );
        }

        $data = array(
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

        return $data;
    }

    public function getPoints()
    {
        $rows = DB::table('instagram_points')->where('media_id', $this->id)->get();

        $points = array();
        foreach ($rows as $row) {
            $points[] = $this->transformPointRow($row);
        }

        return $points;
    }

    protected function applyDataPoints(&$rows)
    {
        
        $mediaIds = array_filter(array_map(function($media) {
            return (isset($media['id']) && !empty($media['id'])) ? $media['id'] : '';
        }, $rows));

        // get all points and map them

        $points = DB::table('instagram_points')
            ->whereIn('media_id', $mediaIds)
            ->orderBy('media_id', 'desc')
            ->orderBy('number', 'asc')
            ->get();

        $map = array();
        foreach ($points as $point) {
            $data = $this->transformPointRow($point);
            $mediaId = $data['mediaId'];
            if (!isset($map[$mediaId])) {
                $map[$mediaId] = array();
            }
            $map[$mediaId][] = $this->transformPointRow($point);
        }

        // apply for all media $rows
        while (list($key, ) = each ($rows)) {
            $mediaId = $rows[$key]['id'];
            if (isset($map[$mediaId])) {
                $rows[$key]['points'] = $map[$mediaId];
            }
        }
    }

    // alias to instagram format
    protected function transformRow($row)
    {
        $data = array(
            'id' => $row->id,
            'images' => array(
                'thumbnail' => array(
                    'url' => app('voyager')->image($row->thumbnail_url),
                    'width' => $row->thumbnail_width,
                    'height' => $row->thumbnail_height
                ),
                'standard_resolution' => array(
                    'url' => app('voyager')->image($row->url),
                    'width' => $row->width,
                    'height' => $row->height
                )
            )
        );

        //item.images.standard_resolution.url

        return $data;
    }

    public function getSyncData($url = '')
    {
        if (!$url) {
            $queryString = http_build_query(array(
                'count' => $this->getItemCountPerPage()
            ));
            $url = route('instagram.index.load') . '?' . $queryString;
        }
        return $this->request($url);
    }

    protected function _queryBuilderWhere($query)
    {
        // date may be equal more than one row
        $query->orderBy('created_at', 'desc') ;
        $query->orderBy('id', 'desc') ;

        // @todo where `enabled` and `count`
        $query->where('enabled', 1);
    }

    public function request($url)
    {
        $data = array(
            'pagination' => array(),
            'data' => array(),
            'meta' => array('code' => 200)
        );

        $parts = parse_url($url);
        parse_str($parts['query'], $params);

        // params
        $count = @$params['count'] ?: 10;
        $maxId = @$params['max_id'];


        // query
        $query = DB::table('instagram_media');
        $this->_queryBuilderWhere($query);

        if ($maxId) {
            $query->where('id', '<', $maxId);
        }

        if ($count) {
            $query->limit($count);
        }

        // debug query
        #echo $query->toSql();

        $rows = $query->get();

        // transform
        foreach ($rows as $row) {
            $data['data'][] = $this->transformRow($row);
        }

        // pagination
        if (count($data['data'])) {

            $this->applyDataPoints($data['data']);

            $nextMaxId = end($data['data'])['id'];

            // make sure record must found
            $query = DB::table('instagram_media');
            $this->_queryBuilderWhere($query);
            $query->where('id', '<', $nextMaxId);
            $query->limit($count);

            $last = $query->first();
            if ($last) {

                $unparse_url = function (array $parsed) {
                    $get = function ($key) use ($parsed) {
                        return isset($parsed[$key]) ? $parsed[$key] : null;
                    };

                    $pass      = $get('pass');
                    $user      = $get('user');
                    $userinfo  = $pass !== null ? "$user:$pass" : $user;
                    $port      = $get('port');
                    $scheme    = $get('scheme');
                    $query     = $get('query');
                    $fragment  = $get('fragment');
                    $authority =
                        ($userinfo !== null ? "$userinfo@" : '') .
                        $get('host') .
                        ($port ? ":$port" : '');

                    return
                        (strlen($scheme) ? "$scheme:" : '') .
                        (strlen($authority) ? "//$authority" : '') .
                        $get('path') .
                        (strlen($query) ? "?$query" : '') .
                        (strlen($fragment) ? "#$fragment" : '');
                };

                $params['max_id'] = $nextMaxId;
                $parts['query'] = http_build_query($params);
                $nextUrl = $unparse_url($parts);

                $data['pagination'] = array(
                    'next_max_id' => $nextMaxId,
                    'next_url' => $nextUrl
                );
            }
        }

        return $data;
    }
}