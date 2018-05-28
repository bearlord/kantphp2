<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Cookie;

use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Support\Arr;

final class Cookie
{

	/**
	 *
	 * @var config
	 */
	protected $config;
	protected $request;

	/**
	 * The default path (if specified).
	 *
	 * @var string
	 */
	public $path = '/';

	/**
	 * The default domain (if specified).
	 *
	 * @var string
	 */
    public $domain = null;

	/**
	 * The default secure setting (defaults to false).
	 *
	 * @var bool
	 */
    public $secure = false;

	/**
	 * All of the cookies queued for sending.
	 *
	 * @var array
	 */
    public $queued = [];

	public function __construct($config)
	{
		$this->setDefaultPathAndDomain($config['path'], $config['domain'], $config['secure']);
	}

	public function handle()
	{
		
	}

	/**
	 * Create a new cookie instance.
	 *
	 * @param string $name            
	 * @param string $value            
	 * @param int $minutes            
	 * @param string $path            
	 * @param string $domain            
	 * @param bool $secure            
	 * @param bool $httpOnly            
	 * @return \Kant\Http\Cookie
	 */
	public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
	{
		list ($path, $domain, $secure) = $this->getPathAndDomain($path, $domain, $secure);

		$time = ($minutes == 0) ? 0 : time() + ($minutes * 60);
		return new \Kant\Http\Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Create a cookie that lasts "forever" (five years).
	 *
	 * @param string $name            
	 * @param string $value            
	 * @param string $path            
	 * @param string $domain            
	 * @param bool $secure            
	 * @param bool $httpOnly            
	 * @return \Kant\View\Http\Cookie
	 */
	public function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true)
	{
		return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Expire the given cookie.
	 *
	 * @param string $name            
	 * @param string $path            
	 * @param string $domain            
	 * @return \Symfony\Component\HttpFoundation\Cookie
	 */
	public function forget($name, $path = null, $domain = null)
	{
		return $this->make($name, null, - 2628000, $path, $domain);
	}

	/**
	 * Determine if a cookie has been queued.
	 *
	 * @param string $key            
	 * @return bool
	 */
	public function hasQueued($key)
	{
		return !is_null($this->queued($key));
	}

	/**
	 * Get a queued cookie instance.
	 *
	 * @param string $key            
	 * @param mixed $default            
	 * @return \Symfony\Component\HttpFoundation\Cookie
	 */
	public function queued($key, $default = null)
	{
		return Arr::get($this->queued, $key, $default);
	}

	/**
	 * Queue a cookie to send with the next response.
	 *
	 * @param
	 *            mixed
	 * @return void
	 */
	public function queue()
	{
		$cookie = call_user_func_array([
			$this,
			'make'
				], func_get_args());
		$this->queued[$cookie->getName()] = $cookie;
	}

	/**
	 * Remove a cookie from the queue.
	 *
	 * @param string $name            
	 * @return void
	 */
	public function unqueue($name)
	{
		unset($this->queued[$name]);
	}

	/**
	 * Get the path and domain, or the default values.
	 *
	 * @param string $path            
	 * @param string $domain            
	 * @param bool $secure            
	 * @return array
	 */
	protected function getPathAndDomain($path, $domain, $secure = false)
	{
		return [
			$path ?: $this->path,
			$domain ?: $this->domain,
			$secure ?: $this->secure
		];
	}

	/**
	 * Set the default path and domain for the jar.
	 *
	 * @param string $path            
	 * @param string $domain            
	 * @param bool $secure            
	 * @return $this
	 */
	public function setDefaultPathAndDomain($path, $domain, $secure = false)
	{
		list ($this->path, $this->domain, $this->secure) = [
			$path,
			$domain,
			$secure
		];

		return $this;
	}

	/**
	 * Get the cookies which have been queued for the next request.
	 *
	 * @return array
	 */
	public function getQueuedCookies()
	{
		return $this->queued;
	}

}
