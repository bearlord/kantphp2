<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @original-author Laravel/Symfony
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Session;

use Kant\Http\Request;

interface Session
{

    /**
     * Starts the session storage.
     *
     * @return bool True if session started
     *        
     * @throws \RuntimeException If session fails to start.
     */
    public function start();

    /**
     * Returns the session ID.
     *
     * @return string The session ID
     */
    public function getId();

    /**
     * Sets the session ID.
     *
     * @param string $id            
     */
    public function setId($id);

    /**
     * Returns the session name.
     *
     * @return mixed The session name
     */
    public function getName();

    /**
     * Sets the session name.
     *
     * @param string $name            
     */
    public function setName($name);

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int $lifetime
     *            Sets the cookie lifetime for the session cookie. A null value
     *            will leave the system settings unchanged, 0 sets the cookie
     *            to expire with browser session. Time is in seconds, and is
     *            not a Unix timestamp.
     *            
     * @return bool True if session invalidated, false if error
     */
    public function invalidate($lifetime = null);

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy
     *            Whether to delete the old session or leave it to garbage collection
     * @param int $lifetime
     *            Sets the cookie lifetime for the session cookie. A null value
     *            will leave the system settings unchanged, 0 sets the cookie
     *            to expire with browser session. Time is in seconds, and is
     *            not a Unix timestamp.
     *            
     * @return bool True if session migrated, false if error
     */
    public function migrate($destroy = false, $lifetime = null);

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     */
    public function save();

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name
     *            The attribute name
     *            
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has($name);

    /**
     * Returns an attribute.
     *
     * @param string $name
     *            The attribute name
     * @param mixed $default
     *            The default value if not found
     *            
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Sets an attribute.
     *
     * @param string $name            
     * @param mixed $value            
     */
    public function set($name, $value);

    /**
     * Returns attributes.
     *
     * @return array Attributes
     */
    public function all();

    /**
     * Sets attributes.
     *
     * @param array $attributes
     *            Attributes
     */
    public function replace(array $attributes);

    /**
     * Removes an attribute.
     *
     * @param string $name            
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove($name);

    /**
     * Clears all attributes.
     */
    public function clear();

    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Registers a SessionBagInterface with the session.
     *
     * @param SessionBagInterface $bag            
     */
    public function registerBag(SessionBagInterface $bag);

    /**
     * Gets a bag instance by name.
     *
     * @param string $name            
     *
     * @return SessionBagInterface
     */
    public function getBag($name);

    /**
     * Gets session meta.
     *
     * @return MetadataBag
     */
    public function getMetadataBag();

    /**
     * Get the session handler instance.
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler();

    /**
     * Determine if the session handler needs a request.
     *
     * @return bool
     */
    public function handlerNeedsRequest();

    /**
     * Set the request on the handler instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request            
     * @return void
     */
    public function setRequestOnHandler(Request $request);
}
