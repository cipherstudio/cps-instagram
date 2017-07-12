<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class InstagramSync extends Model
{
    const SESSION_ACCESS_TOKEN_KEY = 'instagram_access_token';
    const COOKIE_ACCESS_TOKEN_KEY = 'instagram_access_token';

    /**
     * @var array $config
     */
    protected $config;

    /**
     * @var string $accessToken
     */
    protected $accessToken;

    /**
     * @var integer $itemCountPerPage
     */
    protected $itemCountPerPage = 10;

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = config('instagram');
        }

        return $this->config;
    }

    public function init()
    {
        // @fixed
        // @see   view set cookie
        $accessToken = @$_COOKIE[\App\InstagramSync::COOKIE_ACCESS_TOKEN_KEY] ?: '';
        $this->setAccessToken($accessToken);

        $config = $this->getConfig();
        $this->setItemCountPerPage($config['page_size']);
    }

    public function hasAccessToken()
    {
        return isset($this->accessToken) && !empty($this->accessToken);
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getAccessTokenUrl()
    {
        $config = $this->getConfig();

        $args = $config['oauth']['app'];
        $args['state'] = rand(100, 999);

        $url = $config['oauth']['url'] . '?' .http_build_query($args);
        return $url;
    }

    public function getApiUrl()
    {
        $config = $this->getConfig();
        return $config['api']['url'];
    }

    public function getEndpointUrl($name)
    {
        $config = $this->getConfig();
        return $this->getApiUrl() . '/' . $config['api']['endpoints'][$name];
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

    public function request($url)
    {
        $headers = [];
        $headers[] = 'Accept: application/json; charset=utf-8';
        #$headers[]  = 'Authorization: Bearer ' . $this->getAccessToken();

        $data = null;

        try {
            $s = curl_init(); 

            curl_setopt($s, CURLOPT_URL, $url);
            curl_setopt($s, CURLOPT_HTTPHEADER, $headers); 
            curl_setopt($s, CURLOPT_TIMEOUT, 0); 
            curl_setopt($s, CURLOPT_RETURNTRANSFER, true); 

            curl_setopt($s,CURLOPT_HEADER,true); 

            # @fixed trig for test error
            #curl_setopt($s,CURLOPT_POSTFIELDS, array()); 

            $response = curl_exec($s); 
            $status = curl_getinfo($s, CURLINFO_HTTP_CODE); 

            $headerSize = curl_getinfo($s, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            if ($status != 200) {
                $errorMsg = trim(explode("\n", $header)[0]);
                throw new \Exception($errorMsg);
            }

            curl_close($s); 

            $data = json_decode($body, true);

        } catch (\Exception $e) {

            //@todo handle data
            zf_dump($e->getMessage());
        }

        return $data;
    }

    public function getSyncData($url = '')
    {
        if (!$url) {
            $accountUrl = $this->getEndpointUrl('get_current_account') . '?' . http_build_query(array('access_token' => $this->getAccessToken()));
            $data = $this->request($accountUrl);

            $userId = $data['data']['id'];

            $queryString = http_build_query(array(
                'access_token' => $this->getAccessToken(), 
                'count' => $this->getItemCountPerPage()
            ));
            $url = $this->getApiUrl() . '/users/' . $userId . '/media/recent' . '?' . $queryString;
        }
        
        $data = $this->request($url);

        return $data;
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

        $input = array(
            'name' => '',
            'type' => 'image',
            'width' => '640',
            'height' => '640',
            'thumbnail_width' => '320',
            'thumbnail_height' => '320',
            'data' => null,
            'enabled' => 'on',
            'created_at' => $now,
            'updated_at' => $now
        );

        foreach ($uploadedFileMap as $targetKey => $sourceKey) {
            if (isset($item[$sourceKey]) && !empty($item[$sourceKey])) {
                $input[$targetKey] = $this->createUploadedFile($item[$sourceKey]);
            }
        }

        $input['name'] = $input[$targetKey]->getClientOriginalName();

        // @fixed timestamp field
        foreach ( array('created_at', 'updated_at', 'deleted_at') as $key) {
            if (isset($input[$key]) && !empty($input[$key])) {
                $data->$key = strtotime($input[$key]);
                unset($input[$key]);
            }
        }

        $request->replace($input);
        $request->files->replace(array('url' => $input['url'], 'thumbnail_url' => $input['thumbnail_url']));

        return $data;
    }

    
}