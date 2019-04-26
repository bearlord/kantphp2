<?php
namespace Kant\Session;

use SessionHandlerInterface;
use Kant\Http\Request;
use Kant\Cookie\Cookie;

class CookieSessionHandler implements SessionHandlerInterface
{

    /**
     * The cookie jar instance.
     *
     * @var \Kant\Cookie\Cookie
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var \Kant\Http\Request
     */
    protected $request;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param \Kant\Cookie\Cookie $cookie            
     * @param int $minutes            
     * @return void
     */
    public function __construct(Cookie $cookie, $minutes)
    {
        $this->cookie = $cookie;
        $this->minutes = $minutes;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function close()
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function read($sessionId)
    {
        $value = $this->request->cookies->get($sessionId) ?  : '';
        if (!is_null($decoded = json_decode($value, true)) && is_array($decoded)) {
            if (isset($decoded['expires']) && time() <= $decoded['expires']) {
                return $decoded['data'];
            }
        }
        return '';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function write($sessionId, $data)
    {
        $this->cookie->queue($sessionId, json_encode([
            'data' => $data,
            'expires' => time() + 60 * $this->minutes
        ]));
        
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function destroy($sessionId)
    {
        $this->cookie->queue($this->cookie->forget($sessionId));
        
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Set the request instance.
     *
     * @param \Kant\Http\Request $request            
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
