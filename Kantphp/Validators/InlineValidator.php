<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Validators;

/**
 * InlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * The validation method must have the following signature:
 *
 * ```php
 * function foo($attribute, $params)
 * ```
 *
 * where `$attribute` refers to the name of the attribute being validated, while `$params`
 * is an array representing the additional parameters supplied in the validation rule.
 *
 */
class InlineValidator extends Validator {

    /**
     * @var string|\Closure an anonymous function or the name of a model class method that will be
     * called to perform the actual validation. The signature of the method should be like the following,
     * where `$attribute` is the name of the attribute to be validated, and `$params` contains the value
     * of [[params]] that you specify when declaring the inline validation rule:
     *
     * ```php
     * function foo($attribute, $params)
     * ```
     */
    public $method;

    /**
     * @var mixed additional parameters that are passed to the validation method
     */
    public $params;

    /**
     * @var string|\Closure an anonymous function or the name of a model class method that returns the client validation code.
     * The signature of the method should be like the following:
     *
     * ```php
     * function foo($attribute, $params)
     * {
     *     return "javascript";
     * }
     * ```
     *
     * where `$attribute` refers to the attribute name to be validated.
     *
     * Please refer to [[clientValidateAttribute()]] for details on how to return client validation code.
     */
    public $clientValidate;

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        }
        call_user_func($method, $attribute, $this->params);
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        if ($this->clientValidate !== null) {
            $method = $this->clientValidate;
            if (is_string($method)) {
                $method = [$model, $method];
            }

            return call_user_func($method, $attribute, $this->params);
        } else {
            return null;
        }
    }

}
