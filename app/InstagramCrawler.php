<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class InstagramCrawler extends Model
{
    /**
     * @var array $config
     */
    protected $config;

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = config('instagram');
        }

        return $this->config;
    }

    protected function getType($type)
    {
        return array('GraphImage' => 'image', 'GraphSidecar' => 'carousel', 'GraphVideo' => 'video')[$type];
    }

    protected function createUploadedFile($url)
    {
        $tempDir = sys_get_temp_dir();

        $tempFile = tempnam($tempDir, '');
        file_put_contents($tempFile, file_get_contents($url));
        $contents = file_get_contents($tempFile);

        $path = $tempFile;
        $originalName = basename($url);
        $mimeType = 'application/octet-stream';
        $size = strlen($contents);
        $error = ($size > 0);
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile($path, $originalName, $mimeType, $size, $error);

        // @see Illuminate\Http\Concerns\InteractsWithInput.php
        return \Illuminate\Http\UploadedFile::createFromBase($file);
    }

    public function createInput(Request $request, array $item, $data)
    {
        // simulate post input
        $now = date('F jS, Y h:i A');
        $uploadedFileMap = array('thumbnail_url' => 'url', 'url' => 'hd_url');

        $caption = isset($item['caption']) ? $item['caption'] : @$item['edge_media_to_caption']['edges'][0]['node']['text'];

        $input = array(
            'uid'              => $item['id'],
            'name'             => '', // use file name for this case
            'caption'          => $caption,
            'type'             => $this->getType($item['__typename']),
            'width'            => $item['dimensions']['width'],
            'height'           => $item['dimensions']['height'],
            'thumbnail_width'  => $item['thumbnail_resources'][0]['config_width'],
            'thumbnail_height' => $item['thumbnail_resources'][0]['config_height'],
            'data'             => json_encode($item),
            'enabled'          => 'off',
            'count'            => 0,
            'sort'             => isset($item['date']) ? $item['date'] : $item['taken_at_timestamp'], // used for sort according by instagram feed
            'created_at'       => $now,
            'updated_at'       => $now
        );

        // @todo deprecated url and hd_url
        $item['url'] = $item['thumbnail_resources'][0]['src'];
        $item['hd_url'] = isset($item['display_src']) ? $item['display_src'] : $item['display_url'];

        foreach ($uploadedFileMap as $targetKey => $sourceKey) {
            if (isset($item[$sourceKey]) && !empty($item[$sourceKey])) {
                $input[$targetKey] = $this->createUploadedFile($item[$sourceKey]);
            }
        }

        // last file from foreach()
        $input['name'] = $input[$targetKey]->getClientOriginalName();

        // @fixed timestamp field
        foreach ( array('created_at', 'updated_at', 'deleted_at') as $key) {
            if (isset($input[$key]) && !empty($input[$key])) {
                $data->$key = strtotime($input[$key]);
                unset($input[$key]);
            }
        }

        // @see yoyager BREAD checkbox 
        if ($input['enabled'] == 'off') {
            unset($input['enabled']);
        }

        $request->replace($input);
        $request->files->replace(array('url' => $input['url'], 'thumbnail_url' => $input['thumbnail_url']));

        return $data;
    }

}