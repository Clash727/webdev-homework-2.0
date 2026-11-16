<?php
/** 用途：通过原创封面卡片浏览 FIFA 官方视频入口。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '精彩进球';
?>
<section class="cup-page"><header class="page-heading"><h1>精彩进球</h1></header>
<?= $this->render('../shared/_cup-tabs') ?>
<div class="highlight-grid">
    <?php foreach ($highlights as $highlight): ?>
        <article class="highlight-card"><a class="highlight-art highlight-<?= Html::encode($highlight['theme']) ?>" href="<?= Html::encode($highlight['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="在 FIFA 观看<?= Html::encode($highlight['title']) ?>"><span class="highlight-code"><?= Html::encode($highlight['codes'][0]) ?><br><?= Html::encode($highlight['codes'][1]) ?></span><span class="highlight-orbit" aria-hidden="true"></span><span class="play-icon" aria-hidden="true">↗</span><span class="highlight-tag">FIFA 官方视频</span></a><div class="highlight-copy"><p><?= Html::encode($highlight['label']) ?></p><h2><?= Html::encode($highlight['title']) ?></h2><?= Html::a('前往官方观看 ↗', $highlight['url'], ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?></div></article>
    <?php endforeach; ?>
</div>
<div class="next-chapter"><?= Html::a('球迷讨论 →', ['world-cup-hub/fans'], ['class' => 'text-link']) ?></div>
</section>
