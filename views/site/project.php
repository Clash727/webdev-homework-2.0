<?php
/** 用途：世界杯项目介绍与四位成员的分工、成果。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '项目经历';
$people = [];
foreach ($team ? $team->members : [] as $member) {
    if ($member->contribution_key !== null) { $people[$member->contribution_key] = $member; }
}
?>
<section class="project-page">
    <header class="page-heading"><p class="eyebrow">项目经历</p><h1>把一届世界杯，<br>做成我们的作品。</h1><p>以 2026 世界杯为主题，连接赛事浏览、团队展示与球迷互动。</p></header>
    <div class="project-overview"><div class="project-mark" aria-hidden="true">20<span>26</span><i>✳</i></div><div class="project-overview-copy"><h2>2026 世界杯专题站</h2><p>让比赛有清楚的数据，让团队有自己的面孔，让每一份贡献都能被看见。</p><div class="technology-list"><span>PHP / Yii2</span><span>MySQL</span><span>响应式前端</span></div><?= Html::a('浏览世界杯专题 ↗', ['world-cup/index'], ['class' => 'text-link']) ?></div></div>
    <section class="section-block" aria-labelledby="division-heading"><div class="section-heading"><h2 id="division-heading">四份分工，一个作品</h2><?= Html::a('认识团队成员 ↗', ['team/index'], ['class' => 'text-link']) ?></div>
        <div class="contribution-grid">
            <?php foreach ($portfolio as $key => $profile): $person = $people[$key] ?? null; ?>
                <article class="contribution-card"><div class="contribution-top"><span><?= Html::encode($profile['number']) ?></span><strong><?= Html::encode($person ? $person->display_name : '成员' . $profile['number']) ?></strong></div><h3><?= Html::encode($profile['role']) ?></h3><p><?= Html::encode($profile['summary']) ?></p><ul class="scope-list"><?php foreach ($profile['scope'] as $scope): ?><li><?= Html::encode($scope) ?></li><?php endforeach; ?></ul><div class="contribution-works"><h4>已实现成果</h4><div class="work-links"><?php foreach ($profile['works'] as $work): ?><?= Html::a(Html::encode($work['title']) . ' ↗', $work['url'], is_array($work['url']) ? [] : ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?><?php endforeach; ?></div></div></article>
            <?php endforeach; ?>
        </div>
    </section>
</section>
