<?php
/** 用途：世界杯专题内的三页导航。作者：zhixiu-feng */
use yii\helpers\Html;
$current = Yii::$app->controller->getRoute();
$tabs = [
    ['赛程与赛果', ['world-cup/index'], str_starts_with($current, 'world-cup/')],
    ['精彩进球', ['world-cup-hub/highlights'], $current === 'world-cup-hub/highlights'],
    ['球迷讨论', ['world-cup-hub/fans'], $current === 'world-cup-hub/fans' || str_starts_with($current, 'guestbook/')],
];
?>
<nav class="cup-tabs" aria-label="世界杯栏目">
    <?php foreach ($tabs as [$label, $url, $active]): ?>
        <?= Html::a($label, $url, ['class' => $active ? 'is-active' : null, 'aria-current' => $active ? 'page' : null]) ?>
    <?php endforeach; ?>
</nav>
