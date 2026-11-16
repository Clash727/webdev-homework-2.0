<?php
/**
 * 用途：世界杯主题首页，复用公开赛果及课设团队资料。
 * 作者：zhixiu-feng
 */

$this->title = '2026 世界杯';
use yii\helpers\Html;
?>
<section class="home-hero">
    <div class="hero-copy">
        <p class="eyebrow">2026 世界杯 · 精选赛事</p>
        <h1>为每一次<br><span>精彩喝彩。</span></h1>
        <p class="hero-intro">比赛、进球与球迷的声音，在这里相遇。</p>
        <div class="button-row"><?= Html::a('查看赛事 <span aria-hidden="true">↗</span>', ['world-cup/index'], ['class' => 'primary-link']) ?><?= Html::a('认识我们的团队 <span aria-hidden="true">→</span>', ['team/index'], ['class' => 'text-link']) ?></div>
    </div>
    <div class="arena-art" role="img" aria-label="深蓝球场、足球与2026主题图形">
        <span class="arena-year" aria-hidden="true">26</span>
        <svg class="arena-field" viewBox="0 0 560 400" aria-hidden="true"><path d="m85 120 315-45 150 250-510 40Z" fill="#29497d" fill-opacity=".25" stroke="#6688be"/><path d="m240 98 56 245M85 120l315-45M40 365l510-40" stroke="#6688be" fill="none"/><ellipse cx="267" cy="211" rx="65" ry="40" transform="rotate(-7 267 211)" fill="none" stroke="#6688be"/><path d="m91 184 78-9 44 126-143 10M442 147l-67 9 43 115 91-9" stroke="#6688be" fill="none"/></svg>
        <div class="arena-ball"><svg viewBox="0 0 200 200" aria-hidden="true"><defs><radialGradient id="ball-light" cx="30%" cy="25%"><stop stop-color="#fff"/><stop offset="1" stop-color="#c4d0e0"/></radialGradient><clipPath id="ball-edge"><circle cx="100" cy="100" r="94"/></clipPath></defs><circle cx="100" cy="100" r="94" fill="url(#ball-light)"/><g clip-path="url(#ball-edge)" fill="#111e37"><path d="m100 61 36 26-14 42H78L64 87Z M100-5l25 20-10 27H85L75 15Z M2 48l30-10 21 24-13 28H10Z M198 48l-30-10-21 24 13 28h30Z M13 143l37-8 20 28-10 32H25Z M187 143l-37-8-20 28 10 32h35Z M100 183l23 17H77Z"/><path d="m100 42v19M53 62l11 25M147 62l-11 25M50 135l28-6M150 135l-28-6M70 163l30 20 30-20" fill="none" stroke="#111e37" stroke-width="2"/></g></svg></div>
        <span class="arena-ticket">11 JUN <i>—</i> 19 JUL <b>2026</b></span>
    </div>
</section>
<section class="home-results section-block" aria-labelledby="latest-results">
    <div class="section-heading"><h2 id="latest-results">精选赛果</h2><?= Html::a('全部赛程与赛果 ↗', ['world-cup/index'], ['class' => 'text-link']) ?></div>
    <div class="results-grid">
        <?php foreach ($results as $fixture): ?><?= $this->render('../shared/_result-card', ['fixture' => $fixture]) ?><?php endforeach; ?>
        <?php if ($results === []): ?><p class="empty-state">暂无已发布赛果。</p><?php endif; ?>
    </div>
    <p class="time-note">比赛时间均为北京时间（UTC+08:00）。</p>
</section>
<section class="home-stories section-block" aria-label="世界杯专题入口">
    <a class="story-tile story-goals" href="<?= Html::encode(yii\helpers\Url::to(['world-cup-hub/highlights'])) ?>"><span class="story-symbol" aria-hidden="true">▶</span><div><h2>精彩进球</h2><p>重温官方集锦</p></div><span class="story-arrow" aria-hidden="true">↗</span></a>
    <a class="story-tile story-fans" href="<?= Html::encode(yii\helpers\Url::to(['world-cup-hub/fans'])) ?>"><span class="story-symbol" aria-hidden="true">“</span><div><h2>球迷看台</h2><p>分享你的看球感受</p></div><span class="story-arrow" aria-hidden="true">↗</span></a>
</section>
<section class="home-team section-block">
    <div><p class="eyebrow">专题背后的我们</p><h2>四个人，<br>同一座看台。</h2><p>从数据到界面，把对足球的热爱写进代码。</p><?= Html::a('看看我们做了什么 ↗', ['site/project'], ['class' => 'text-link']) ?></div>
    <div class="team-mini-list">
        <?php foreach ($team ? $team->members : [] as $member): ?>
            <a href="<?= Html::encode(yii\helpers\Url::to(['team/member', 'id' => $member->id])) ?>"><span class="mini-number"><?= Html::encode($member->contribution['number'] ?? '·') ?></span><strong><?= Html::encode($member->display_name) ?></strong><span><?= Html::encode($member->role) ?></span><span aria-hidden="true">↗</span></a>
        <?php endforeach; ?>
        <?php if ($team === null || $team->members === []): ?><?= Html::a('认识我们的团队 →', ['team/index']) ?><?php endif; ?>
    </div>
</section>
