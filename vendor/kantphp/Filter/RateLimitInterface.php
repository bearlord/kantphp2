<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Filter;

/**
 * RateLimitInterface is the interface that may be implemented by an identity object to enforce rate limiting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface RateLimitInterface
{

    /**
     * Returns the maximum number of allowed requests and the window size.
     * 
     * @param \Kant\Http\Request $request
     *            the current request
     * @param \Kant\Action\Action $action
     *            the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     *         and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action);

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * 
     * @param \Kant\Http\Request $request
     *            the current request
     * @param \Kant\Action\Action $action
     *            the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     *         and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action);

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * 
     * @param \Kant\Http\Request $request
     *            the current request
     * @param \Kant\Http\Action $action
     *            the action to be executed
     * @param int $allowance
     *            the number of allowed requests remaining.
     * @param int $timestamp
     *            the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp);
}
