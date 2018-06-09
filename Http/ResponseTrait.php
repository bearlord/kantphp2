<?php
namespace Kant\Http;

use Kant\Kant;
use Exception;
use Kant\Http\Exceptions\HttpResponseException;

trait ResponseTrait
{

    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * The exception that triggered the error response (if applicable).
     *
     * @var \Exception|null
     */
    public $exception;

    /**
     * Get the status code for the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        return $this->original;
    }

    /**
     * Set a header on the Response.
     *
     * @param string $key            
     * @param array|string $values            
     * @param bool $replace            
     * @return $this
     */
    public function header($key, $values, $replace = true)
    {
        $this->headers->set($key, $values, $replace);
        
        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param array $headers            
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }
        
        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param \Kant\Http\Cookie|mixed $cookie            
     * @return $this
     */
    public function cookie($cookie)
    {
        return call_user_func_array([
            $this,
            'withCookie'
        ], func_get_args());
    }

    /**
     * Add a cookie to the response.
     *
     * @param \Kant\Http\Cookie|mixed $cookie            
     * @return $this
     */
    public function withCookie($cookie, $b = 100)
    {
        if (is_array($cookie)) {
            foreach ($cookie as $vcookie) {
                if (is_string($vcookie)) {
                    $vcookie = call_user_func_array([
                        Kant::$app->getCookie(),
                        'make'
                    ], func_get_args());
                }
                
                $this->headers->setCookie($vcookie);
            }
            return $this;
        }
        if (is_string($cookie)) {
            $cookie = call_user_func_array([
                Kant::$app->getCookie(),
                'make'
            ], func_get_args());
        }
        
        $this->headers->setCookie($cookie);
        
        return $this;
    }

    /**
     * Set the exception to attach to the response.
     *
     * @param \Exception $e            
     * @return $this
     */
    public function withException(Exception $e)
    {
        $this->exception = $e;
        
        return $this;
    }

    /**
     * Throws the response in a HttpResponseException instance.
     *
     * @throws \Kant\Http\Exceptions\HttpResponseException
     */
    public function throwResponse()
    {
        throw new HttpResponseException($this);
    }
}
