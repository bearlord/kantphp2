<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Session;

use Kant\Kant;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Http\Cookie;
use Kant\Session\SessionManager;
use Kant\Support\Arr;

final class StartSession
{

    protected $config;

    protected $manager;

    protected $request;

    protected $response;

    /**
     * Indicates if the session was handled for the current request.
     *
     * @var bool
     */
    protected $sessionHandled = false;

    public function __construct($config)
    {
        $this->config = $config;
        $this->request = Kant::$app->request;
        $this->response = Kant::$app->response;
    }

    /**
     * Register the session manager instance.
     *
     * @return void
     */
    public function handle()
    {
        $this->manager = new SessionManager($this->config);
        $this->sessionHandled = true;
        // If a session driver has been configured, we will need to start the session here
        // so that the data is ready for an application. Note that the Laravel sessions
        // do not make use of PHP "native" sessions in any way since they are crappy.
        if ($this->sessionConfigured()) {
            $session = $this->startSession($this->request);
            $this->request->setSession($session);
            
            $this->collectGarbage($session);
        }
        
        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        if ($this->sessionConfigured()) {
            $this->storeCurrentUrl($this->request, $session);
            
            $this->addCookieToResponse($this->response, $session);
        }
        
        return $session;
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        return ! is_null(Arr::get($this->manager->getSessionConfig(), 'driver'));
    }

    /**
     * Start the session for the given request.
     *
     * @param \Kant\Http\Request $request            
     * @return \Kant\Session\SessionInterface
     */
    protected function startSession(Request $request)
    {
        $session = $this->getSession($request);
        
        $session->setRequestOnHandler($request);
        
        $session->start();
        
        return $session;
    }

    /**
     * Get the session implementation from the manager.
     *
     * @param \Kant\Http\Request $request            
     * @return \Kant\Session\SessionInterface
     */
    public function getSession(Request $request)
    {
        $session = $this->manager->driver();
        $session->setId($request->cookies->get($session->getName()));
        return $session;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \Kant\Http\Request $request            
     * @param \Kant\Session\SessionInterface $session            
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if ($request->method() === 'GET' && $request->route() && ! $request->ajax()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param \Kant\Session\SessionInterface $session            
     * @return void
     */
    protected function collectGarbage($session)
    {
        $config = $this->manager->getSessionConfig();
        
        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param array $config            
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        return mt_rand(1, $config['lottery'][1]) <= $config['lottery'][0];
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param \Kant\Http\Response $response            
     * @param \Kant\Session\SessionInterface $session            
     * @return void
     */
    protected function addCookieToResponse(Response $response, Session $session)
    {
        if ($this->usingCookieSessions()) {
            $this->manager->driver()->save();
        }
        
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
            $response->headers->setCookie(new Cookie($session->getName(), $session->getId(), $this->getCookieExpirationDate(), $config['path'], $config['domain'], Arr::get($config, 'secure', false), Arr::get($config, 'http_only', true)));
        }
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds()
    {
        return Arr::get($this->manager->getSessionConfig(), 'lifetime') * 60;
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();
        
        return $config['expire_on_close'] ? 0 : time() + $config['lifetime'];
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @param array|null $config            
     * @return bool
     */
    protected function sessionIsPersistent(array $config = null)
    {
        $config = $config ?  : $this->manager->getSessionConfig();
        
        return ! in_array($config['driver'], [
            null,
            'array'
        ]);
    }

    /**
     * Determine if the session is using cookie sessions.
     *
     * @return bool
     */
    protected function usingCookieSessions()
    {
        if (! $this->sessionConfigured()) {
            return false;
        }
        
        return $this->manager->driver()->getHandler() instanceof CookieSessionHandler;
    }
}

?>
