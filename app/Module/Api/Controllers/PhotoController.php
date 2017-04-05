<?php

namespace App\Api\Controllers;

use Kant\Controller\Controller;
use Kant\Http\Response;

class PhotoController extends Controller {

    public function indexAction() {
        return [
            'status' => 2000,
            'message' => 'OK',
            'data' => ['hello', 'world']
        ];
    }

    public function showAction(Response $response) {
        $response->format = Response::FORMAT_XML;
         return [
            'status' => 2000,
            'message' => 'OK',
            'data' => ['hello', 'world']
        ];
    }

}
