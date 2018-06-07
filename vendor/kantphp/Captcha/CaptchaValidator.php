<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\captcha;

use Kant\Kant;
use Kant\Exception\InvalidConfigException;
use Kant\Validators\ValidationAsset;
use Kant\Validators\Validator;

/**
 * CaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 *
 * CaptchaValidator should be used together with [[CaptchaAction]].
 *
 * Note that once CAPTCHA validation succeeds, a new CAPTCHA will be generated automatically. As a result,
 * CAPTCHA validation should not be used in AJAX validation mode because it may fail the validation
 * even if a user enters the same code as shown in the CAPTCHA image which is actually different from the latest CAPTCHA code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaValidator extends Validator
{

    /**
     *
     * @var boolean whether to skip this validator if the input is empty.
     */
    public $skipOnEmpty = false;

    /**
     *
     * @var boolean whether the comparison is case sensitive. Defaults to false.
     */
    public $caseSensitive = false;

    /**
     *
     * @var string the route of the controller action that renders the CAPTCHA image.
     */
    public $captchaAction = 'common/service/captcha';

    /**
     *
     * @var string layout
     */
    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Kant::t('kant', 'The verification code is incorrect.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = ! is_array($value) && $captcha->validate($value, $this->caseSensitive);
        
        return $valid ? null : [
            $this->message,
            []
        ];
    }

    /**
     * Creates the CAPTCHA action object from the route specified by [[captchaAction]].
     * 
     * @return \Kant\Captcha\CaptchaAction the action object
     * @throws InvalidConfigException
     */
    public function createCaptchaAction()
    {
        $ca = Kant::$app->createController($this->captchaAction);
        if ($ca !== false) {
            /* @var $controller \Kant\Controller\Controller */
            list ($controller, $actionID) = $ca;
            $controller->layout = $this->layout;
            $controller->view->layout = $this->layout;
            $action = $controller->createActions($actionID);
            if ($action !== null) {
                return $action;
            }
        }
        throw new InvalidConfigException('Invalid CAPTCHA action ID: ' . $this->captchaAction);
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        
        return 'kant.validation.captcha(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * @inheritdoc
     */
    public function getClientOptions($model, $attribute)
    {
        $captcha = $this->createCaptchaAction();
        $code = $captcha->getVerifyCode(false);
        $hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
        $options = [
            'hash' => $hash,
            'hashKey' => 'kantCaptcha/' . $captcha->getUniqueId() . '/' . $captcha->id,
            'caseSensitive' => $this->caseSensitive,
            'message' => Kant::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute)
            ], Kant::$app->language)
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        
        return $options;
    }
}
