<?php

namespace App\Http\Controllers\Instagram;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;

class CrawlerController extends \TCG\Voyager\Http\Controllers\VoyagerBreadController
{

    /**
     * @var \App\InstagramSync $sync
     */
    private $crawler;

    public function __construct(\App\InstagramCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function load(Request $request)
    {
        zf_dump(__METHOD__);
        $crawler = new \Smochin\Instagram\Crawler();

        // $user = $crawler->getUser('cpschaps');
        // zf_dump($user, '$user');

        // error_reporting(E_ALL & ~E_NOTICE);
        $media = $crawler->getMediaByUser('cpschaps');
        zf_dump($media, '$media');
    }

    public function install(Request $request)
    {
        if (env('APP_ENV') != 'local') return;

        $items = json_decode(file_get_contents(base_path() . '/docs/scripts/data.json'), true);

        /*
        $count = count($items);
        $types = array();

        while (list(, $node) = each($items)) {
            if (!in_array($node['__typename'], $types)) {
                $types[] = $node['__typename'];
                ${$node['__typename']} = 0;
            }
            ${$node['__typename']}++;
        }

        zf_dump($count, '$count');
        foreach ($types as $type) {
            zf_dump($$type, $type);
        }

        reset($items);
        zf_dump(current($items), '$first');
        zf_dump(end($items), '$last');
        exit;
        //*/


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

        $count = 0;
        $limit = 5;

        $media = array();
        foreach ($items as $item) {

            $count++;

            // zf_dump($item['id']);
            // if ($item['id'] == '826744422365339270') {
            //     zf_dump($item);
            //     exit;
            // }

            if (in_array($item['id'], array(
                '872201555088973575',
                '826744422365339270'
            ))) {

                // 872201555088973575: https://www.instagram.com/p/warrF3nx8H/?taken-by=cpschaps
                // 826744422365339270: https://www.instagram.com/p/t5L6v_Hx6G/?taken-by=cpschaps
                continue;
            }

            // @fixed duplicated
            $uid = $item['id'];

            // @todo make UID (tail owner)
            //UPDATE `instagram_media` SET uid = CONCAT(uid, '_209201990');
            $uid = $uid . '_209201990'; 

            $query = DB::table('instagram_media');
            $row = $query->where('uid', $uid)
                ->limit(1)
                ->first();
            if ($row) {
                // zf_dump('skip duplicated');
                continue;
            }


            // $newRequest = clone($request);
            $newRequest = new Request();

            $data = $this->crawler->createInput($newRequest, $item, new $dataType->model_name());
            $data = $this->insertUpdateData($newRequest, $slug, $dataType->addRows, $data);
            $media[] = $data->toArray();
        }

        return response()->json($media);
    }

}