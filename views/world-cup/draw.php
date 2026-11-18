<?php

use yii\helpers\Html;

$this->registerCssFile('@web/css/world-cup.css');
$this->registerCssFile('@web/css/world-cup-draw.css');
?>
<section class="wc-page wc-draw">
    <header class="wc-head"><h1><?= Html::encode($this->title) ?></h1></header>
    <?= $this->render('../shared/_cup-tabs') ?>
    <?= $this->render('_match-nav', ['active' => $viewName]) ?>

    <?php if ($hasDemo || $source === 'demo'): ?>
        <nav class="draw-switch" aria-label="赛事来源">
            <?= Html::a('官方', ['world-cup/draw', 'view' => $viewName], ['aria-current' => $source === 'official' ? 'page' : null]) ?>
            <?= Html::a('演示', ['world-cup/draw', 'view' => $viewName, 'source' => 'demo'], ['aria-current' => $source === 'demo' ? 'page' : null]) ?>
        </nav>
    <?php endif; ?>

    <?php if ($matches === []): ?>
        <p class="wc-empty">对阵待确认</p>
    <?php else: ?>
        <?= $this->render($viewName === 'groups' ? '_groups' : '_bracket', ['matches' => $matches]) ?>
    <?php endif; ?>
</section>
