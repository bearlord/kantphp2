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
use Kant\Support\Str;
use Kant\Exception\InvalidArgumentException;

class SessionManager
{

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Get the default session driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return Kant::$app->config->get('session.driver');
    }

    public function driver($driver = null)
    {
        $driver = $driver ?  : $this->getDefaultDriver();
        
        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }
        
        return $this->drivers[$driver];
    }

    public function createDriver($driver)
    {
        $method = 'create' . Str::studly($driver) . 'Driver';
        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } elseif (method_exists($this, $method)) {
            return $this->$method();
        }
        
        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver            
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->buildSession($this->customCreators[$driver]());
    }

    /**
     * Create an instance of the "array" session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createArrayDriver()
    {
        return $this->buildSession(new NullSessionHandler());
    }

    /**
     * Create an instance of the "cookie" session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createCookieDriver()
    {
        $lifetime = Kant::$app->config->get('session.lifetime');
        
        return $this->buildSession(new CookieSessionHandler(Kant::$app->cookie, $lifetime));
    }

    /**
     * Create an instance of the file session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createFileDriver()
    {
        return $this->createNativeDriver();
    }

    /**
     * Create an instance of the file session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createNativeDriver()
    {
        $files = $this->getFileSystem();

        $path = Kant::getAlias(Kant::$app->config->get('session.files'));
        
        $lifetime = Kant::$app->config->get('session.lifetime');
        
        return $this->buildSession(new FileSessionHandler($files, $path, $lifetime));
    }

    /**
     * Get the file system for the file driver
     */
    protected function getFileSystem()
    {
        return Kant::$app->files;
    }

    /**
     * Create an instance of the database session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createDatabaseDriver()
    {
        $connection = $this->getDatabaseConnection();
        
        $table = Kant::$app->config->get('session.table');
        
        $lifetime = Kant::$app->config->get('session.maxlifetime');
        
        return $this->buildSession(new DatabaseSessionHandler($connection, $table, $lifetime));
    }

    /**
     * Get the database connection for the database driver.
     *
     * @return \Kant\Database\Connection
     */
    protected function getDatabaseConnection()
    {
        return Kant::$app->getDb();
    }

    /**
     * Create an instance of the APC session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createApcDriver()
    {
        return $this->createCacheBased('apc');
    }

    /**
     * Create an instance of the Memcached session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createMemcachedDriver()
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Wincache session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createWincacheDriver()
    {
        return $this->createCacheBased('wincache');
    }

    /**
     * Create an instance of the Redis session driver.
     *
     * @return \Kant\Session\Store
     */
    protected function createRedisDriver()
    {
        $handler = $this->createCacheHandler('redis');
        
        $handler->getCache()
            ->getStore()
            ->setConnection($this->app['config']['session.connection']);
        
        return $this->buildSession($handler);
    }

    /**
     * Create an instance of a cache driven driver.
     *
     * @param string $driver            
     * @return \Kant\Session\Store
     */
    protected function createCacheBased($driver)
    {
        return $this->buildSession($this->createCacheHandler($driver));
    }

    /**
     * Create the cache based session handler instance.
     *
     * @param string $driver            
     * @return \Kant\Session\CacheBasedSessionHandler
     */
    protected function createCacheHandler($driver)
    {
        $minutes = Kant::$app->config->get('session.lifetime');
        
        return new CacheBasedSessionHandler(clone $this->app['cache']->driver($driver), $minutes);
    }

    /**
     * Build the session instance.
     *
     * @param \SessionHandlerInterface $handler            
     * @return \Kant\Session\Store
     */
    protected function buildSession($handler)
    {
        if (Kant::$app->config->get('session.encrypt')) {
            return new EncryptedStore(Kant::$app->config->get('cookie'), $handler, $this->app['encrypter']);
        } else {
            return new Store(Kant::$app->config->get('session.cookie'), $handler);
        }
    }

    /**
     * Get the session configuration.
     *
     * @return array
     */
    public function getSessionConfig()
    {
        return Kant::$app->config->get('session');
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method            
     * @param array $parameters            
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([
            $this->driver(),
            $method
        ], $parameters);
    }
}
