<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Filter\Auth;

use Kant\Identity\User;
use Kant\Http\Request;
use Kant\Http\Response;
use Kant\Identity\IdentityInterface;
use Kant\Exception\UnauthorizedHttpException;

/**
 * AuthInterface is the interface that should be implemented by auth method classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface AuthInterface
{

    /**
     * Authenticates the current user.
     * 
     * @param User $user            
     * @param Request $request            
     * @param Response $response            
     * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
     * @throws UnauthorizedHttpException if authentication information is provided but is invalid.
     */
    public function authenticate($user, $request, $response);

    /**
     * Generates challenges upon authentication failure.
     * For example, some appropriate HTTP headers may be generated.
     * 
     * @param Response $response            
     */
    public function challenge($response);

    /**
     * Handles authentication failure.
     * The implementation should normally throw UnauthorizedHttpException to indicate authentication failure.
     * 
     * @param Response $response            
     * @throws UnauthorizedHttpException
     */
    public function handleFailure($response);
}
