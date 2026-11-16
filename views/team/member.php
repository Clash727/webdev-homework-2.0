<?php
/**
 * 用途：课设成员简介与个人公开链接。
 * 作者：zhixiu-feng
 */

use yii\helpers\Html;

/** @var app\models\ProjectMember $member */
$this->title = $member->display_name;
$this->registerCssFile('@web/css/team.css');
$links = array_filter($member->links, static function ($link) {
    return $link->isPublicUrl();
});
?>
<article class="team-page team-member-page profile-<?= Html::encode($member->contribution['number'] ?? '00') ?>">
    <?= Html::a('返回我们的团队', ['team/index'], ['class' => 'team-back']) ?>
    <header class="team-heading">
        <h1><?= Html::encode($member->display_name) ?></h1>
        <p class="team-role"><?= Html::encode($member->role) ?></p>
    </header>
    <?php if ($member->bio !== ''): ?>
        <div class="team-bio"><?= nl2br(Html::encode($member->bio), false) ?></div>
    <?php endif; ?>
    <?php if ($member->contribution !== null): ?>
        <section class="member-contribution" aria-labelledby="contribution-heading">
            <h2 id="contribution-heading">我的项目分工</h2>
            <ul class="scope-list"><?php foreach ($member->contribution['scope'] as $scope): ?><li><?= Html::encode($scope) ?></li><?php endforeach; ?></ul>
            <h3>已实现成果</h3>
            <div class="work-links"><?php foreach ($member->contribution['works'] as $work): ?><?= Html::a(Html::encode($work['title']) . ' ↗', $work['url'], is_array($work['url']) ? [] : ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?><?php endforeach; ?></div>
        </section>
    <?php endif; ?>
    <?php if ($links !== []): ?>
        <nav class="team-member-links" aria-label="成员公开链接">
            <?php foreach ($links as $link): ?>
                <?= Html::a(Html::encode($link->title), $link->url, [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                ]) ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</article>
