<?php
/** 用途：安全呈现保存提示及表单错误。作者：zhixiu-feng */
use yii\helpers\Html;
$message = Yii::$app->session->getFlash('profileSaved');
?>
<?php if ($message !== null): ?><div class="editor-success" role="status"><?= Html::encode($message) ?></div><?php endif; ?>
<?= Html::errorSummary($editor->errorModels(), ['class' => 'editor-errors', 'header' => '请检查以下内容：', 'role' => 'alert']) ?>
