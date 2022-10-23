<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use KubAT\PhpSimple\HtmlDomParser;
use Throwable;

class WebsiteDomParserController extends Controller
{
    public function __invoke()
    {
        try {
            $response = Http::get(config('website_dom.url'));

            $result = [];
            if ($response->successful()) {
                $dom = HtmlDomParser::str_get_html($response->body());
                if (!empty($dom->find('table tr'))) {
                    $data_img = $dom->find('table tr div > img');
                    foreach ($data_img as $key => $img) {
                        $result[$key]['img'] = ($img->tag == 'img') ? $img->attr['src'] : '';
                    }
                    $data_title = $dom->find('table tr div > a');
                    foreach ($data_title as $key => $item) {
                        $result[$key]['href'] = ($item->tag == 'a') ? $item->attr['href'] : '';

                        $title = $item->nodes[0]->innertext;
                        $result[$key]['title'] = ($item->tag == 'a') ? self::tis_620_to_utf_8($title) : '';
                    }
                    $data_view = $dom->find('table tr');
                    foreach ($data_view as $key => $view) {
                        $result[$key]['view'] = self::tis_620_to_utf_8($view->children(1)->children(0)->nodes[1]->innertext) ?? '';
                    }
                }

                return response()->json([
                    'status'  => true,
                    'data'    => $result,
                    'message' => 'success'
                ]);
            }
        } catch (Throwable $exception) {
            return response()->json([
                'status'  => false,
                'data'    => [],
                'message' => $exception->getMessage()
            ]);
        }

    }

    private function tis_620_to_utf_8($title)
    {
        $title = str_replace(["\r", "\n", "\t"], '', $title);
        return iconv("TIS-620", "UTF-8", $title);
    }


}
