<?php
namespace Kant\Http;

use BadMethodCallException;
use Kant\Support\Str;
use Kant\Support\MessageBag;
use Kant\Support\ViewErrorBag;
use Kant\Session\Store as SessionStore;
use Kant\Support\MessageProvider;
use Kant\Http\File\UploadedFile;
use Kant\Http\BaseRedirectResponse;

class RedirectResponse extends BaseRedirectResponse
{
    
    use ResponseTrait;

    /**
     * The request instance.
     *
     * @var \Kant\Http\Request
     */
    protected $request;

    /**
     * The session store implementation.
     *
     * @var \Kant\Session\Store
     */
    protected $session;

    /**
     * Flash a piece of data to the session.
     *
     * @param string|array $key            
     * @param mixed $value            
     * @return \Kant\Http\RedirectResponse
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [
            $key => $value
        ];
        
        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }
        
        return $this;
    }

    /**
     * Add multiple cookies to the response.
     *
     * @param array $cookies            
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }
        
        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param array $input            
     * @return $this
     */
    public function withInput(array $input = null)
    {
        $this->session->flashInput($this->removeFilesFromInput($input ?  : $this->request->input()));
        
        return $this;
    }

    /**
     * Remove all uploaded files form the given input array.
     *
     * @param array $input            
     * @return array
     */
    protected function removeFilesFromInput(array $input)
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->removeFilesFromInput($value);
            }
            
            if ($value instanceof UploadedFile) {
                unset($input[$key]);
            }
        }
        
        return $input;
    }

    /**
     * Flash an array of input to the session.
     *
     * @return $this
     */
    public function onlyInput()
    {
        return $this->withInput($this->request->only(func_get_args()));
    }

    /**
     * Flash an array of input to the session.
     *
     * @return \Kant\Http\RedirectResponse
     */
    public function exceptInput()
    {
        return $this->withInput($this->request->except(func_get_args()));
    }

    /**
     * Flash a container of errors to the session.
     *
     * @param \Kant\Support\MessageProvider|array|string $provider            
     * @param string $key            
     * @return $this
     */
    public function withErrors($provider, $key = 'default')
    {
        $value = $this->parseErrors($provider);
        $this->session->flash('errors', $this->session->get('errors', new ViewErrorBag())
            ->put($key, $value));
        
        return $this;
    }

    /**
     * Parse the given errors into an appropriate value.
     *
     * @param \Kant\Support\MessageProvider|array|string $provider            
     * @return \Kant\Support\MessageBag
     */
    protected function parseErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            return $provider->getMessageBag();
        }
        
        return new MessageBag((array) $provider);
    }

    /**
     * Get the original response content.
     *
     * @return null
     */
    public function getOriginalContent()
    {
        //
    }

    /**
     * Get the request instance.
     *
     * @return \Kant\Http\Request|null
     */
    public function getRequest()
    {
        return $this->request;
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

    /**
     * Get the session store implementation.
     *
     * @return \Kant\Session\Store|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the session store implementation.
     *
     * @param \Kant\Session\Store $session            
     * @return void
     */
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }

    /**
     * Dynamically bind flash data in the session.
     *
     * @param string $method            
     * @param array $parameters            
     * @return $this
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }
        
        throw new BadMethodCallException("Method [$method] does not exist on Redirect.");
    }
}
