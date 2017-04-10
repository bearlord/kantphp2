<?php

use Kant\Widget\ActiveForm;
use Kant\Helper\Html;
?>
<div class="panel-body">
    <?php $form = ActiveForm::begin('', 'post', ['csrf' => true]); ?>
    <?= $form->field($model, 'p_title')->textInput(); ?>
    <?= $form->field($model, 'p_content')->passwordInput(); ?>
    <?=
    $form->field($model, 'verifyCode')->widget(\Kant\Captcha\Captcha::classname(), [
            // configure additional widget properties here
    ])
    ?>
    <div class="form-group">
    <?= Html::submitButton('注册', ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
</div>