<?php
/** 用途：复用公开比赛的比分卡片。作者：zhixiu-feng */
use yii\helpers\Html;
/** @var app\models\Fixture $fixture */
?>
<article class="result-card">
    <div class="result-meta"><span><?= Html::encode($fixture->group_name ? $fixture->group_name . ' 组 · 小组赛' : $fixture->stageLabel()) ?></span><span><?= Html::encode($fixture->kickoffAtBeijing()->format('m.d H:i')) ?></span></div>
    <div class="result-teams"><span><?= Html::encode($fixture->homeTeam->code) ?></span><strong><?= Html::encode($fixture->displayScore() ?? '未开赛') ?></strong><span><?= Html::encode($fixture->awayTeam->code) ?></span></div>
    <p class="result-names"><?= Html::encode($fixture->homeTeam->name) ?> <span>vs</span> <?= Html::encode($fixture->awayTeam->name) ?></p>
    <div class="result-bottom"><span><?= $fixture->isDemo() ? '演示数据' : ($fixture->isFinished() ? '已完赛' : '未开赛') ?></span><?= Html::a('比赛详情 ↗', ['world-cup/view', 'id' => $fixture->id]) ?></div>
</article>
