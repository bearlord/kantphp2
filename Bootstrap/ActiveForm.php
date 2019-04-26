<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Bootstrap;

use Kant\Kant;
use Kant\Exception\InvalidConfigException;

/**
 * A Bootstrap 3 enhanced version of [[\Kant\Widget\ActiveForm]].
 *
 * This class mainly adds the [[layout]] property to choose a Bootstrap 3 form layout.
 * So for example to render a horizontal form you would:
 *
 * ```php
 * use Kant\Bootstrap\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal'])
 * ```
 *
 * This will set default values for the [[ActiveField]]
 * to render horizontal form fields. In particular the [[ActiveField::template|template]]
 * is set to `{label} {beginWrapper} {input} {error} {endWrapper} {hint}` and the
 * [[ActiveField::horizontalCssClasses|horizontalCssClasses]] are set to:
 *
 * ```php
 * [
 * 'offset' => 'col-sm-offset-3',
 * 'label' => 'col-sm-3',
 * 'wrapper' => 'col-sm-6',
 * 'error' => '',
 * 'hint' => 'col-sm-3',
 * ]
 * ```
 *
 * To get a different column layout in horizontal mode you can modify those options
 * through [[fieldConfig]]:
 *
 * ```php
 * $form = ActiveForm::begin([
 * 'layout' => 'horizontal',
 * 'fieldConfig' => [
 * 'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
 * 'horizontalCssClasses' => [
 * 'label' => 'col-sm-4',
 * 'offset' => 'col-sm-offset-4',
 * 'wrapper' => 'col-sm-8',
 * 'error' => '',
 * 'hint' => '',
 * ],
 * ],
 * ]);
 * ```
 *
 * @see ActiveField for details on the [[fieldConfig]] options
 * @see http://getbootstrap.com/css/#forms
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @since 2.0
 */
class ActiveForm extends \Kant\Widget\ActiveForm
{

    /**
     *
     * @var string the default field class name when calling [[field()]] to create a new field.
     * @see fieldConfig
     */
    public $fieldClass = 'Kant\Bootstrap\ActiveField';

    /**
     *
     * @var array HTML attributes for the form tag. Default is `['role' => 'form']`.
     */
    public $options = [
        'role' => 'form'
    ];

    /**
     *
     * @var string the form layout. Either 'default', 'horizontal' or 'inline'.
     *      By choosing a layout, an appropriate default field configuration is applied. This will
     *      render the form fields with slightly different markup for each layout. You can
     *      override these defaults through [[fieldConfig]].
     * @see \Kant\Bootstrap\ActiveField for details on Bootstrap 3 field configuration
     */
    public $layout = 'default';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!in_array($this->layout, [
            'default',
            'horizontal',
            'inline'
        ])) {
            throw new InvalidConfigException('Invalid layout type: ' . $this->layout);
        }
        
        if ($this->layout !== 'default') {
            Html::addCssClass($this->options, 'form-' . $this->layout);
        }
        parent::init();
    }

    /**
     * @inheritdoc
     * 
     * @return ActiveField the created ActiveField object
     */
    public function field($model, $attribute, $options = [])
    {
        return parent::field($model, $attribute, $options);
    }
}
