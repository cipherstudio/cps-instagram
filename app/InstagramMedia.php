<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

use Illuminate\Support\Facades\Storage;

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
            'sort' => $row->sort,
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
        $config = $this->getConfig();
        $sortBy = 'created_at';
        $sortDir = 'DESC';

        if (isset($config['sort_by']) && isset($config['sort_dir'])) {
            $sortBy = $config['sort_by'];
            $sortDir = $config['sort_dir'];
        }

        // date may be equal more than one row
        $query->orderBy($sortBy, $sortDir);
        // $query->orderBy('id', 'desc') ;

        // @todo where `enabled` and `count`
        $query->where('enabled', 1);
    }

    protected function _queryBuilderWhereMaxId($query, $maxId)
    {
        $config = $this->getConfig();
        $sortBy = $config['sort_by'];
        $sortDir = $config['sort_dir'];

        $query->where($sortBy, ($sortDir == 'DESC' ? '<' : '>'), $maxId);
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
            $this->_queryBuilderWhereMaxId($query, $maxId);
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
            $nextMaxId = end($data['data'])[$this->getConfig()['sort_by']];

            // make sure record must found
            $query = DB::table('instagram_media');
            $this->_queryBuilderWhere($query);
            $this->_queryBuilderWhereMaxId($query, $nextMaxId);

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

    protected function _deleteFileIfExists($path)
    {
        if (Storage::disk(config('voyager.storage.disk'))->exists($path)) {
            Storage::disk(config('voyager.storage.disk'))->delete($path);
        }
    }

    // @see BREAD destroy
    protected function _deletePoints()
    {
        $points = $this->getPoints();
        foreach ($points as $point) {
            $id = $point['id'];

            $slug = 'instagram-points';
            $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

            // Delete Translations, if present
            if (is_bread_translatable($data)) {
                $data->deleteAttributeTranslations($data->getTranslatableAttributes());
            }

            foreach ($dataType->deleteRows as $row) {
                if ($row->type == 'image') {
                    $this->_deleteFileIfExists('/uploads/'.$data->{$row->field});

                    $options = json_decode($row->details);

                    if (isset($options->thumbnails)) {
                        foreach ($options->thumbnails as $thumbnail) {
                            $ext = explode('.', $data->{$row->field});
                            $extension = '.'.$ext[count($ext) - 1];

                            $path = str_replace($extension, '', $data->{$row->field});

                            $thumb_name = $thumbnail->name;

                            $this->_deleteFileIfExists('/uploads/'.$path.'-'.$thumb_name.$extension);
                        }
                    }
                }
            }

            $data->destroy($id);
        }
    }

    public function delete()
    {
        $this->_deletePoints();
        return parent::delete();
    }



}