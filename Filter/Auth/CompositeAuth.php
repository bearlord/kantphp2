<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Filter\Auth;

use Kant\Kant;
use Kant\Exception\InvalidConfigException;

/**
 * CompositeAuth is an action filter that supports multiple authentication methods at the same time.
 *
 * The authentication methods contained by CompositeAuth are configured via [[authMethods]],
 * which is a list of supported authentication class configurations.
 *
 * The following example shows how to support three authentication methods:
 *
 * ```php
 * public function behaviors()
 * {
 * return [
 * 'compositeAuth' => [
 * 'class' => \Kant\Filter\Auth\CompositeAuth::className(),
 * 'authMethods' => [
 * \Kant\Filter\Auth\HttpBasicAuth::className(),
 * \Kant\Filters\Auth\QueryParamAuth::className(),
 * ],
 * ],
 * ];
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CompositeAuth extends AuthMethod
{

    /**
     *
     * @var array the supported authentication methods. This property should take a list of supported
     *      authentication methods, each represented by an authentication class or configuration.
     *     
     *      If this property is empty, no authentication will be performed.
     *     
     *      Note that an auth method class must implement the [[\Kant\Filter\Auth\AuthInterface]] interface.
     */
    public $authMethods = [];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return empty($this->authMethods) ? true : parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            if (!$auth instanceof AuthInterface) {
                $this->authMethods[$i] = $auth = Kant::createObject($auth);
                if (!$auth instanceof AuthInterface) {
                    throw new InvalidConfigException(get_class($auth) . ' must implement Kant\Filter\Auth\AuthInterface');
                }
            }
            
            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }
        
        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        foreach ($this->authMethods as $method) {
            /** @var $method AuthInterface */
            $method->challenge($response);
        }
    }
}
