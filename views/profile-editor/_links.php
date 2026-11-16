<?php
/** 用途：团队与成员共用的链接行表单。作者：zhixiu-feng */
use yii\helpers\Html;
/** @var app\models\ProfileEditor $editor */
?>
<section class="editor-panel" aria-labelledby="links-title">
    <div class="editor-section-head"><h2 id="links-title"><?= Html::encode($heading) ?></h2></div>
    <div class="link-rows">
        <?php foreach ($editor->rows as $index => $row): ?>
            <fieldset class="link-row<?= $row->isDeleted() ? ' pending-delete' : '' ?>">
                <legend>链接 <?= $index + 1 ?></legend>
                <?= Html::activeHiddenInput($row, "[$index]id") ?>
                <div class="link-row-fields">
                    <div class="editor-field">
                        <?= Html::activeLabel($row, "[$index]title") ?>
                        <?= Html::activeTextInput($row, "[$index]title", ['class' => 'form-control', 'maxlength' => 100]) ?>
                        <?= Html::error($row, 'title', ['class' => 'help-block']) ?>
                    </div>
                    <div class="editor-field link-url-field">
                        <?= Html::activeLabel($row, "[$index]url") ?>
                        <?= Html::activeTextInput($row, "[$index]url", ['class' => 'form-control', 'maxlength' => 2048, 'placeholder' => 'https://', 'inputmode' => 'url']) ?>
                        <?= Html::error($row, 'url', ['class' => 'help-block']) ?>
                    </div>
                    <div class="editor-field link-order-field">
                        <?= Html::activeLabel($row, "[$index]sort_order") ?>
                        <?= Html::activeInput('number', $row, "[$index]sort_order", ['class' => 'form-control', 'min' => 0, 'max' => 4294967295]) ?>
                        <?= Html::error($row, 'sort_order', ['class' => 'help-block']) ?>
                    </div>
                </div>
                <?php if ($row->id): ?><div class="link-delete-check"><?= Html::activeCheckbox($row, "[$index]delete", ['label' => '删除此链接', 'uncheck' => '0', 'value' => '1']) ?></div><?php endif; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>
    <?= Html::submitButton('＋ 添加链接', ['class' => 'editor-add-link', 'name' => 'intent', 'value' => 'add-link', 'formnovalidate' => true]) ?>
    <p class="editor-hint">顺序越小越靠前。勾选删除后，点击保存才会生效。</p>
</section>
