<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstagramApi extends Model
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

    /*
    public function construct(\Illuminate\Http\Request $request)
    {
        $session = $request->session();
        // if ($accessToken = $session->get(self::SESSION_ACCESS_TOKEN_KEY)) {
        //     $this->setAccessToken($accessToken);
        // }
    }
    //*/

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = config('instagram');
        }

        return $this->config;
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

    public function getItems()
    {
        $url = $this->getEndpointUrl('get_current_account') . '?' . http_build_query(array('access_token' => $this->getAccessToken()));

        $data = $this->request($url);
        // zf_dump($data, '$data');

        $userId = $data['data']['id'];
        $itemCountPerPage = 20;
        $queryString = http_build_query(array(
            'access_token' => $this->getAccessToken(), 
            'count' => $itemCountPerPage
        ));
        $url = $this->getApiUrl() . '/users/' . $userId . '/media/recent' . '?' . $queryString;

        // var queryString = $.param({access_token: params.access_token, count: itemCountPerPage}),
        // url = self._url('users/' + userId + '/media/recent') + '?' + queryString;

        // zf_dump($url, '$url');
        $data = $this->request($url);
        // zf_dump($data, '$data');


        return $data['data'];
    }

}