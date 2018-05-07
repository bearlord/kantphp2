<?php

namespace app\module\api\RouteControllers;

use Kant\Controller\Controller;
use Kant\Http\Response;

class PhotoController extends Controller {

    public function indexAction(Response $response) {
        $response->format = Response::FORMAT_XML;
        return [
            'status' => 2000,
            'message' => 'OK',
            'data' => [
                1 => [
                    'name' => 'pic1',
                    'url' => 'http://www.abc.com/pic/1.jpg'
                ],
                2 => [
                    'name' => 'pic2',
                    'url' => 'http://www.abc.com/pic/2.jpg'
                ]
            ]
        ];
    }

    public function showAction($id, Response $response) {
//        $response->format = Response::FORMAT_XML;
        $list = [
            1 => [
                'name' => 'pic1',
                'url' => 'http://www.abc.com/pic/1.jpg'
            ],
            2=> [
                'name' => 'pic2',
                'url' => 'http://www.abc.com/pic/2.jpg'
            ]
        ];
        $data = $list[$id];
        return [
            'status' => 2000,
            'message' => 'OK',
            'data' => $data
        ];
    }

}
