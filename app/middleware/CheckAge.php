<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\middleware;

use Closure;
use Kant\Kant;
use Kant\Http\Response;

/**
 * Description of CheckAge
 *
 * @author zzq
 */
class CheckAge
{

	/**
	 * 返回请求过滤器
	 *
	 * @param \Kant\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, $minAge, Response $response)
	{
		if ($request->input('age') <= $minAge) {
			$response->format = Response::FORMAT_JSON;
			$response->setContent([
				'status' => 400,
				'message' => sprintf("年龄必须大于%s", $minAge)
			]);
			return $response;
		}

		return $next($request);
	}

}
