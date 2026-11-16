<?php
/** 用途：通过原创封面卡片浏览 FIFA 官方视频入口。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '精彩进球';
?>
<section class="cup-page"><header class="page-heading"><p class="eyebrow">2026 世界杯</p><h1>有些瞬间，<br>值得再看一次。</h1></header>
<?= $this->render('../shared/_cup-tabs') ?>
<div class="highlight-grid">
    <?php foreach ($highlights as $highlight): ?>
        <article class="highlight-card"><a class="highlight-art highlight-<?= Html::encode($highlight['theme']) ?>" href="<?= Html::encode($highlight['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="在 FIFA 观看<?= Html::encode($highlight['title']) ?>"><span class="highlight-code"><?= Html::encode($highlight['codes'][0]) ?><br><?= Html::encode($highlight['codes'][1]) ?></span><span class="highlight-orbit" aria-hidden="true"></span><span class="play-icon" aria-hidden="true">↗</span><span class="highlight-tag">FIFA 官方视频</span></a><div class="highlight-copy"><p><?= Html::encode($highlight['label']) ?></p><h2><?= Html::encode($highlight['title']) ?></h2><?= Html::a('前往官方观看 ↗', $highlight['url'], ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?></div></article>
    <?php endforeach; ?>
</div><p class="time-note">原创封面为栏目视觉，点击前往 FIFA 官方观看。</p>
<div class="next-chapter"><span>精彩不止在球场。</span><?= Html::a('去球迷讨论区 →', ['world-cup-hub/fans'], ['class' => 'text-link']) ?></div>
</section>
