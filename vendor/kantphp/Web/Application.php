<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Web;

use Kant\Kant;
use Kant\Exception\InvalidRouteException;
use Kant\Exception\NotFoundHttpException;

/**
 * Application is the base class for all web application classes.
 *
 * For more details and usage information on Application, see the [guide article on applications](guide:structure-applications).
 *
 * @property Request $request The request component. This property is read-only.
 * @property Response $response The response component. This property is read-only.
 * @property Session $session The session component. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \Kant\Foundation\Application
{

	/**
	 * @inheritdoc
	 */
	protected function bootstrap()
	{
		$request = $this->getRequest();

		Kant::setAlias('@webroot', dirname($request->getScriptName()));
		Kant::setAlias('@web', $request->getBaseUrl());
		parent::bootstrap();
	}

    /**
     * Handles the specified request.
     * @param Request $request the request to be handled
     * @return Response the resulting response
     * @throws NotFoundHttpException if the requested route is invalid
     */
    public function handleRequest($request)
    {
        try {
            $this->setCookie($this->config->get('cookie'), $request);
            $this->setSession($this->config->get('session'), $request);
            $response = $this->getRouter()->dispatch($request);
            return $response;
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException(Kant::t('kant', 'Page not found.'), $e, $e->getCode());
        }

    }


    /**
     * Returns the router component
     *
     * @return type
     */
    public function getRouter()
    {
        return $this->get('router');
    }

	private $_homeUrl;

	/**
	 *
	 * @return string the homepage URL
	 */
	public function getHomeUrl()
	{
		if ($this->_homeUrl === null) {
			return $this->getRequest()->getBaseUrl() . '/';
		} else {
			return $this->_homeUrl;
		}
	}

    /**
     * Returns the user component.
     *
     * @return User the user component.
     */
    public function getUser()
    {
        return $this->get('user');
    }

	/**
	 * @inheritdoc
	 */
	public function coreComponents()
	{
		return array_merge(parent::coreComponents(), [
		    'cookie' => [
		        'class' => 'Kant\Cookie\Cookie'
            ],
            'session' => [
                'class' => 'Kant\Session\StartSession'
            ],
            'errorHandler' => [
                'class' => 'Kant\Web\ErrorHandler',
                'errorAction' => '/site/error',
            ],
		]);
	}

}
