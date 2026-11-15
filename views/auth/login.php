<?php
/**
 * 用途：管理员登录表单，不回填密码，不提供注册或记住登录。
 * 作者：Clash727（W05）
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '管理员登录';
?>
<section class="auth-page" aria-labelledby="login-title">
    <p class="eyebrow">WORLD CUP 2026 · 后台</p>
    <h1 id="login-title">管理员登录</h1>
    <p class="note">使用管理员账号进入工作台。</p>
    <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>
        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>
        <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password']) ?>
        <?= Html::submitButton('登录后台', ['class' => 'button']) ?>
    <?php ActiveForm::end(); ?>
</section>
