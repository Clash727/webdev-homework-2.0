<?php

use yii\helpers\Html;

$tabs = [
    'list' => ['时间表', ['world-cup/index']],
    'groups' => ['小组对阵', ['world-cup/draw', 'view' => 'groups']],
    'bracket' => ['淘汰赛', ['world-cup/draw', 'view' => 'bracket']],
];
?>
<nav class="match-nav" aria-label="赛事视图">
    <?php foreach ($tabs as $mode => [$label, $route]): ?>
        <?= Html::a($label, $route, ['aria-current' => $active === $mode ? 'page' : null]) ?>
    <?php endforeach; ?>
</nav>
