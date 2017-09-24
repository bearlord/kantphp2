<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Filter\Auth;

use Kant\Kant;
use Kant\Action\Action;
use Kant\Action\ActionFilter;
use Kant\Exception\UnauthorizedHttpException;
use Kant\Identity\User;
use Kant\Http\Request;
use Kant\Http\Response;

/**
 * AuthMethod is a base class implementing the [[AuthInterface]] interface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class AuthMethod extends ActionFilter implements AuthInterface
{

    /**
     *
     * @var User the user object representing the user authentication status. If not set, the `user` application component will be used.
     */
    public $user;

    /**
     *
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    /**
     *
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;

    /**
     *
     * @var array list of action IDs that this filter will be applied to, but auth failure will not lead to error.
     *      It may be used for actions, that are allowed for public, but return some additional data for authenticated users.
     *      Defaults to empty, meaning authentication is not optional for any action.
     *      Since version 2.0.10 action IDs can be specified as wildcards, e.g. `site/*`.
     * @see isOptional()
     * @since 2.0.7
     */
    public $optional = [];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $response = $this->response ?  : Yii::$app->getResponse();
        
        try {
            $identity = $this->authenticate($this->user ?  : Kant::$app->getUser(), $this->request ?  : Yii::$app->getRequest(), $response);
        } catch (UnauthorizedHttpException $e) {
            if ($this->isOptional($action)) {
                return true;
            }
            
            throw $e;
        }
        
        if ($identity !== null || $this->isOptional($action)) {
            return true;
        } else {
            $this->challenge($response);
            $this->handleFailure($response);
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {}

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }

    /**
     * Checks, whether authentication is optional for the given action.
     *
     * @param Action $action
     *            action to be checked.
     * @return bool whether authentication is optional or not.
     * @see optional
     * @since 2.0.7
     */
    protected function isOptional($action)
    {
        $id = $this->getActionId($action);
        foreach ($this->optional as $pattern) {
            if (fnmatch($pattern, $id)) {
                return true;
            }
        }
        return false;
    }
}
