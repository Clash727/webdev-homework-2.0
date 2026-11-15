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
<article class="team-page team-member-page">
    <?= Html::a('返回我们的团队', ['team/index'], ['class' => 'team-back']) ?>
    <header class="team-heading">
        <h1><?= Html::encode($member->display_name) ?></h1>
        <p class="team-role"><?= Html::encode($member->role) ?></p>
    </header>
    <?php if ($member->bio !== ''): ?>
        <div class="team-bio"><?= nl2br(Html::encode($member->bio), false) ?></div>
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
