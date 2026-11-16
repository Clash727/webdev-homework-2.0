<?php
/** 用途：团队资料、资源链接与四人编辑入口。作者：zhixiu-feng */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '团队资料管理';
$this->registerCssFile('@web/css/profile-editor.css');
?>
<section class="profile-editor">
    <div class="editor-breadcrumb"><?= Html::a('管理工作台', ['admin/index']) ?><span>/</span><span>团队资料</span></div>
    <header class="editor-heading"><div><h1>团队资料</h1><p>维护团队介绍与公开资源。</p></div><?= Html::a('查看公开页面 ↗', ['team/index'], ['class' => 'editor-preview', 'target' => '_blank', 'rel' => 'noopener']) ?></header>
    <?= $this->render('../profile-editor/_feedback', ['editor' => $editor]) ?>
    <?php $form = ActiveForm::begin(['id' => 'team-editor', 'enableClientValidation' => false]); ?>
        <section class="editor-panel">
            <h2>基本资料</h2>
            <?= $form->field($team, 'name')->textInput(['maxlength' => 100])->label('团队名称') ?>
            <?= $form->field($team, 'summary')->textarea(['rows' => 5, 'maxlength' => 5000])->label('团队简介') ?>
        </section>
        <?= $this->render('../profile-editor/_links', ['editor' => $editor, 'heading' => '团队资源链接']) ?>
        <div class="editor-actions"><?= Html::submitButton('保存团队资料', ['class' => 'button', 'name' => 'intent', 'value' => 'save']) ?><?= Html::a('返回工作台', ['admin/index'], ['class' => 'text-link']) ?></div>
    <?php ActiveForm::end(); ?>
    <section class="editor-member-list" aria-labelledby="edit-members-title"><div class="editor-section-head"><h2 id="edit-members-title">成员资料</h2></div>
        <?php foreach ($team->members as $person): ?>
            <div class="editor-member-row"><span class="editor-member-number"><?= Html::encode($person->contribution['number'] ?? '·') ?></span><div><strong><?= Html::encode($person->display_name) ?></strong><p><?= Html::encode($person->role) ?></p></div><?= Html::a('编辑成员 ↗', ['admin-member/update', 'id' => $person->id]) ?></div>
        <?php endforeach; ?>
        <?php if ($team->members === []): ?><p class="editor-hint">暂无成员资料。</p><?php endif; ?>
    </section>
</section>
