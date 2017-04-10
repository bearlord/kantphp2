<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Validators;

use Kant\Kant;

/**
 * StringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StringValidator extends Validator {

    /**
     * @var integer|array specifies the length limit of the value to be validated.
     * This can be specified in one of the following forms:
     *
     * - an integer: the exact length that the value should be of;
     * - an array of one element: the minimum length that the value should be of. For example, `[8]`.
     *   This will overwrite [[min]].
     * - an array of two elements: the minimum and maximum lengths that the value should be of.
     *   For example, `[8, 128]`. This will overwrite both [[min]] and [[max]].
     * @see tooShort for the customized message for a too short string.
     * @see tooLong for the customized message for a too long string.
     * @see notEqual for the customized message for a string that does not match desired length.
     */
    public $length;

    /**
     * @var integer maximum length. If not set, it means no maximum length limit.
     * @see tooLong for the customized message for a too long string.
     */
    public $max;

    /**
     * @var integer minimum length. If not set, it means no minimum length limit.
     * @see tooShort for the customized message for a too short string.
     */
    public $min;

    /**
     * @var string user-defined error message used when the value is not a string.
     */
    public $message;

    /**
     * @var string user-defined error message used when the length of the value is smaller than [[min]].
     */
    public $tooShort;

    /**
     * @var string user-defined error message used when the length of the value is greater than [[max]].
     */
    public $tooLong;

    /**
     * @var string user-defined error message used when the length of the value is not equal to [[length]].
     */
    public $notEqual;

    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
     * If this property is not set, [[\Kant\base\Application::charset]] will be used.
     */
    public $encoding;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->encoding === null) {
            $this->encoding = Kant::$app->charset;
        }
        if ($this->message === null) {
            $this->message = Kant::t('kant', '{attribute} must be a string.');
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = Kant::t('kant', '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.');
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = Kant::t('kant', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.');
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual = Kant::t('kant', '{attribute} should contain {length, number} {length, plural, one{character} other{characters}}.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, $this->tooLong, ['max' => $this->max]);
        }
        if ($this->length !== null && $length !== $this->length) {
            $this->addError($model, $attribute, $this->notEqual, ['length' => $this->length]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value) {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }
        if ($this->max !== null && $length > $this->max) {
            return [$this->tooLong, ['max' => $this->max]];
        }
        if ($this->length !== null && $length !== $this->length) {
            return [$this->notEqual, ['length' => $this->length]];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => Kant::$app->getI18n()->format($this->message, [
                'attribute' => $label,
                    ], Kant::$app->language),
        ];

        if ($this->min !== null) {
            $options['min'] = $this->min;
            $options['tooShort'] = Kant::$app->getI18n()->format($this->tooShort, [
                'attribute' => $label,
                'min' => $this->min,
                    ], Kant::$app->language);
        }
        if ($this->max !== null) {
            $options['max'] = $this->max;
            $options['tooLong'] = Kant::$app->getI18n()->format($this->tooLong, [
                'attribute' => $label,
                'max' => $this->max,
                    ], Kant::$app->language);
        }
        if ($this->length !== null) {
            $options['is'] = $this->length;
            $options['notEqual'] = Kant::$app->getI18n()->format($this->notEqual, [
                'attribute' => $label,
                'length' => $this->length,
                    ], Kant::$app->language);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'kant.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

}
