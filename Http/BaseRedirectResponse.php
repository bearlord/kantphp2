<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http;

/**
 * RedirectResponse represents an HTTP response doing a redirect.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BaseRedirectResponse extends BaseResponse
{

    protected $targetUrl;

    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string $url
     *            The URL to redirect to. The URL should be a full URL, with schema etc.,
     *            but practically every browser redirects on paths only as well
     * @param int $status
     *            The status code (302 by default)
     * @param array $headers
     *            The headers (Location is always set to the given URL)
     *            
     * @throws \InvalidArgumentException
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3
     */
    public function __construct($url, $status = 302, $headers = array())
    {
        parent::__construct('', $status, $headers);
        
        $this->setTargetUrl($url);
        
        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
        
        if (301 == $status && ! array_key_exists('cache-control', $headers)) {
            $this->headers->remove('cache-control');
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function create($url = '', $status = 302, $headers = array())
    {
        return new static($url, $status, $headers);
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string $url
     *            The URL to redirect to
     *            
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }
        
        $this->targetUrl = $url;
        
        $this->setContent(sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));
        
        $this->headers->set('Location', $url);
        
        return $this;
    }
}
