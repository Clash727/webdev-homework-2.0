<?php
/** 用途：精简留言表单、回执与公开列表。作者：Clash727（W08） */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '留言';
$this->registerCssFile('@web/css/guestbook.css', ['depends' => app\assets\AppAsset::class]);
$receipt = Yii::$app->session->getFlash('guestbookSubmitted');
?>
<section class="guestbook-page">
    <header class="page-heading"><h1>留言</h1></header>
    <?= $this->render('../shared/_cup-tabs') ?>
    <div class="guestbook-layout">
        <section class="guestbook-compose" aria-label="写留言">
            <?php if ($receipt !== null): ?>
                <p class="guestbook-receipt" role="status"><?= Html::encode($receipt) ?></p>
            <?php endif; ?>
            <?php $form = ActiveForm::begin([
                'id' => 'guestbook-form', 'action' => ['guestbook/create'],
                'enableClientValidation' => false,
            ]); ?>
                <?= $form->field($model, 'nickname')->textInput([
                    'maxlength' => 80, 'autocomplete' => 'nickname', 'required' => true,
                ]) ?>
                <?= $form->field($model, 'body')->textarea([
                    'maxlength' => 2000, 'rows' => 5, 'required' => true,
                ]) ?>
                <?= Html::submitButton('提交留言', ['class' => 'button']) ?>
            <?php ActiveForm::end(); ?>
        </section>
        <section class="guestbook-messages" aria-label="公开留言">
            <?php if ($messages === []): ?><p class="guestbook-empty">暂无留言</p><?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <?= $this->render('../shared/_guest-message', ['message' => $message]) ?>
            <?php endforeach; ?>
            <?= LinkPager::widget([
                'pagination' => $pagination, 'options' => ['class' => 'guestbook-pager'],
                'prevPageLabel' => '上一页', 'nextPageLabel' => '下一页', 'maxButtonCount' => 5,
            ]) ?>
        </section>
    </div>
</section>
