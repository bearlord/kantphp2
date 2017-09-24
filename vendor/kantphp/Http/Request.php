<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http;

use Kant\Kant;
use Kant\Helper\StringHelper;

class Request extends BaseRequest
{

    /**
     * The name of the HTTP header for sending CSRF token.
     */
    const CSRF_HEADER = 'X-CSRF-Token';

    /**
     * The length of the CSRF token mask.
     */
    const CSRF_MASK_LENGTH = 8;

    /**
     *
     * @var boolean whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to true.
     *      When CSRF validation is enabled, forms submitted to an Kant Web application must be originated
     *      from the same application. If not, a 400 HTTP exception will be raised.
     *     
     *      Note, this feature requires that the user client accepts cookie. Also, to use this feature,
     *      forms submitted via POST method must contain a hidden input whose name is specified by [[csrfParam]].
     *      You may use [[\Kant\Helper\Html::beginForm()]] to generate his hidden input.
     *     
     *      In JavaScript, you may get the values of [[csrfParam]] and [[csrfToken]] via `yii.getCsrfParam()` and
     *      `yii.getCsrfToken()`, respectively. The [[\yii\web\KantAsset]] asset must be registered.
     *      You also need to include CSRF meta tags in your pages by using [[\yii\helpers\Html::csrfMetaTags()]].
     *     
     * @see Controller::enableCsrfValidation
     * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
     */
    public $enableCsrfValidation = true;

    /**
     *
     * @var string the name of the token used to prevent CSRF. Defaults to '_csrf'.
     *      This property is used only when [[enableCsrfValidation]] is true.
     */
    public $csrfParam = '_csrf';

    /**
     *
     * @var array the configuration for creating the CSRF [[Cookie|cookie]]. This property is used only when
     *      both [[enableCsrfValidation]] and [[enableCsrfCookie]] are true.
     */
    public $csrfCookie = [
        'httpOnly' => true
    ];

    /**
     *
     * @var boolean whether to use cookie to persist CSRF token. If false, CSRF token will be stored
     *      in session under the name of [[csrfParam]]. Note that while storing CSRF tokens in session increases
     *      security, it requires starting a session for every page, which will degrade your site performance.
     */
    public $enableCsrfCookie = true;

    /**
     *
     * @var boolean whether cookies should be validated to ensure they are not tampered. Defaults to true.
     */
    public $enableCookieValidation = true;

    /**
     *
     * @var string the name of the POST parameter that is used to indicate if a request is a PUT, PATCH or DELETE
     *      request tunneled through POST. Defaults to '_method'.
     */
    public $methodParam = '_method';

    private $_csrfToken;

    /**
     * Returns the token used to perform CSRF validation.
     *
     * This token is generated in a way to prevent [BREACH attacks](http://breachattack.com/). It may be passed
     * along via a hidden field of an HTML form or an HTTP header value to support CSRF validation.
     * 
     * @param boolean $regenerate
     *            whether to regenerate CSRF token. When this parameter is true, each time
     *            this method is called, a new CSRF token will be generated and persisted (in session or cookie).
     * @return string the token used to perform CSRF validation.
     */
    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            if ($regenerate || ($token = $this->loadCsrfToken()) === null) {
                $token = $this->generateCsrfToken();
            }
            // the mask doesn't need to be very random
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-.';
            $mask = substr(str_shuffle(str_repeat($chars, 5)), 0, static::CSRF_MASK_LENGTH);
            // The + sign may be decoded as blank space later, which will fail the validation
            $this->_csrfToken = str_replace('+', '.', base64_encode($mask . $this->xorTokens($token, $mask)));
        }
        
        return $this->_csrfToken;
    }

    /**
     * Loads the CSRF token from cookie or session.
     * 
     * @return string the CSRF token loaded from cookie or session. Null is returned if the cookie or session
     *         does not have CSRF token.
     */
    protected function loadCsrfToken()
    {
        if ($this->enableCsrfCookie) {
            return $this->cookie($this->csrfParam);
        } else {
            return Kant::$app->getSession()->get($this->csrfParam);
        }
    }

    /**
     * Generates an unmasked random token used to perform CSRF validation.
     * 
     * @return string the random token for CSRF validation.
     */
    protected function generateCsrfToken()
    {
        $token = Kant::$app->getSecurity()->generateRandomString();
        if ($this->enableCsrfCookie) {
            $cookie = $this->createCsrfCookie($token);
            Kant::$app->getResponse()->withCookie($cookie);
        } else {
            Kant::$app->getSession()->set($this->csrfParam, $token);
            Kant::$app->getSession()->save();
        }
        return $token;
    }

    /**
     * Returns the XOR result of two strings.
     * If the two strings are of different lengths, the shorter one will be padded to the length of the longer one.
     * 
     * @param string $token1            
     * @param string $token2            
     * @return string the XOR result
     */
    private function xorTokens($token1, $token2)
    {
        $n1 = StringHelper::byteLength($token1);
        $n2 = StringHelper::byteLength($token2);
        if ($n1 > $n2) {
            $token2 = str_pad($token2, $n1, $token2);
        } elseif ($n1 < $n2) {
            $token1 = str_pad($token1, $n2, $n1 === 0 ? ' ' : $token1);
        }
        
        return $token1 ^ $token2;
    }

    /**
     *
     * @return string the CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned if no such header is sent.
     */
    public function getCsrfTokenFromHeader()
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper(static::CSRF_HEADER));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    /**
     * Creates a cookie with a randomly generated CSRF token.
     * Initial values specified in [[csrfCookie]] will be applied to the generated cookie.
     * 
     * @param string $token
     *            the CSRF token
     * @return Cookie the generated cookie
     * @see enableCsrfValidation
     */
    protected function createCsrfCookie($token)
    {
        $options = $this->csrfCookie;
        return new Cookie($this->csrfParam, $token);
    }

    /**
     * Performs the CSRF validation.
     *
     * This method will validate the user-provided CSRF token by comparing it with the one stored in cookie or session.
     * This method is mainly called in [[Controller::beforeAction()]].
     *
     * Note that the method will NOT perform CSRF validation if [[enableCsrfValidation]] is false or the HTTP method
     * is among GET, HEAD or OPTIONS.
     *
     * @param string $token
     *            the user-provided CSRF token to be validated. If null, the token will be retrieved from
     *            the [[csrfParam]] POST field or HTTP header.
     *            This parameter is available since version 2.0.4.
     * @return boolean whether CSRF token is valid. If [[enableCsrfValidation]] is false, this method will return true.
     */
    public function validateCsrfToken($token = null)
    {
        $method = $this->getMethod();
        // only validate CSRF token on non-"safe" methods http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.1.1
        if (! $this->enableCsrfValidation || in_array($method, [
            'GET',
            'HEAD',
            'OPTIONS'
        ], true)) {
            return true;
        }

        $trueToken = $this->loadCsrfToken();

        if ($token !== null) {
            return $this->validateCsrfTokenInternal($token, $trueToken);
        } else {
            return $this->validateCsrfTokenInternal($this->input($this->csrfParam), $trueToken) || $this->validateCsrfTokenInternal($this->getCsrfTokenFromHeader(), $trueToken);
        }
    }

    /**
     * Validates CSRF token
     *
     * @param string $token            
     * @param string $trueToken            
     * @return boolean
     */
    private function validateCsrfTokenInternal($token, $trueToken)
    {
        if (! is_string($token)) {
            return false;
        }

        $token = base64_decode(str_replace('.', '+', $token));
        $n = StringHelper::byteLength($token);
        if ($n <= static::CSRF_MASK_LENGTH) {
            return false;
        }
        $mask = StringHelper::byteSubstr($token, 0, static::CSRF_MASK_LENGTH);
        $token = StringHelper::byteSubstr($token, static::CSRF_MASK_LENGTH, $n - static::CSRF_MASK_LENGTH);
        $token = $this->xorTokens($mask, $token);
        return $token === $trueToken;
    }
}
