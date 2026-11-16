<?php
/**
 * 用途：多彩海报式首页，复用公开赛事和团队数据。
 * 作者：Clash727（W09）；数据接入：zhixiu-feng
 */
use yii\helpers\Html;
use yii\helpers\Url;
$this->title = '2026 世界杯';
?>
<section class="home-hero">
    <div class="hero-copy">
        <h1>球场之上，<br><span>热爱无界。</span></h1>
        <div class="button-row"><?= Html::a('走进世界杯 <span aria-hidden="true">↗</span>', ['world-cup/index'], ['class' => 'primary-link']) ?><?= Html::a('认识我们的团队 →', ['team/index'], ['class' => 'text-link']) ?></div>
    </div>
    <div class="poster-art" aria-hidden="true">
        <div class="poster-orbits"><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <span class="poster-year">26</span>
    </div>
    <div class="hero-edition"><span>CANADA · MÉXICO · USA</span><span>11 JUN — 19 JUL / 2026</span></div>
</section>
<div class="edition-ribbon" aria-hidden="true"></div>
<section class="home-results section-block" aria-labelledby="latest-results">
    <div class="section-heading"><h2 id="latest-results">精选赛果</h2><?= Html::a('全部赛程与赛果 ↗', ['world-cup/index'], ['class' => 'text-link']) ?></div>
    <div class="results-grid">
        <?php foreach ($results as $fixture): ?><?= $this->render('../shared/_result-card', ['fixture' => $fixture]) ?><?php endforeach; ?>
        <?php if ($results === []): ?><p class="empty-state">暂无已发布赛果。</p><?php endif; ?>
    </div>
    <p class="time-note">北京时间（UTC+08:00）</p>
</section>
<section class="home-stories section-block" aria-label="世界杯专题入口">
    <a class="story-tile story-goals" href="<?= Html::encode(Url::to(['world-cup-hub/highlights'])) ?>"><span class="story-symbol" aria-hidden="true">▶</span><h2>精彩进球</h2><span class="story-arrow" aria-hidden="true">↗</span></a>
    <a class="story-tile story-fans" href="<?= Html::encode(Url::to(['world-cup-hub/fans'])) ?>"><span class="story-symbol" aria-hidden="true">“</span><h2>球迷讨论</h2><span class="story-arrow" aria-hidden="true">↗</span></a>
</section>
<section class="home-team section-block">
    <div><h2>我们的团队</h2><?= Html::a('项目分工 ↗', ['site/project'], ['class' => 'text-link']) ?></div>
    <div class="team-mini-list">
        <?php foreach ($team ? $team->members : [] as $member): ?>
            <a href="<?= Html::encode(Url::to(['team/member', 'id' => $member->id])) ?>"><span class="mini-number"><?= Html::encode($member->contribution['number'] ?? '·') ?></span><strong><?= Html::encode($member->display_name) ?></strong><span><?= Html::encode($member->role) ?></span><span aria-hidden="true">↗</span></a>
        <?php endforeach; ?>
        <?php if ($team === null || $team->members === []): ?><?= Html::a('认识我们的团队 →', ['team/index']) ?><?php endif; ?>
    </div>
</section>
