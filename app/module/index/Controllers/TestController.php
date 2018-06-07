<?php

namespace app\module\index\Controllers;

use Kant\Controller\Controller;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Session\Session;

class TestController extends Controller
{
//    public $routePattern = self::ROUTE_PATTERN_EXPLICIT;

	public function indexAction(Response $response, Session $session)
	{
//	    $a /0 ;
		$response->cookie('username', 'zhangsan', 60);
		$session->set('adminid', 1000);
		return 'Hello KantPHP!';
	}

	public function getAction(Request $request, Response $response, Session $session)
	{
		$response->format = Response::FORMAT_JSON;
		return [
			'cookies' => $request->cookies->all(),
			'session' => $session->all()
		];
	}
    
    public function templateAction()
    {
        echo "<h1>Index/Test/Template</h1>";
        return $this->view->render('template');
    }

}
