<?php
/**
 * 用途：课设团队简介与成员分工入口。
 * 作者：zhixiu-feng
 */

use yii\helpers\Html;

/** @var app\models\ProjectTeam $team */
$this->title = '我们的团队';
$this->registerCssFile('@web/css/team.css');
?>
<section class="team-page">
    <header class="team-heading">
        <h1><?= Html::encode($team->name) ?></h1>
        <p class="team-summary"><?= nl2br(Html::encode($team->summary), false) ?></p>
    </header>

    <section class="team-section" aria-labelledby="members-heading">
        <h2 id="members-heading">团队成员</h2>
        <?php if ($team->members === []): ?>
            <p class="team-empty">暂无成员资料。</p>
        <?php else: ?>
            <ul class="team-grid">
                <?php foreach ($team->members as $member): ?>
                    <li class="team-card profile-<?= Html::encode($member->contribution['number'] ?? '00') ?>">
                        <div class="member-portrait" aria-hidden="true"><svg class="jersey-outline" viewBox="0 0 240 230"><path d="m78 32-55 31 24 45 25-11v108h96V97l25 11 24-45-55-31c-5 24-79 24-84 0Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M78 32c12 50 72 50 84 0M84 180h72" fill="none" stroke="currentColor" stroke-width="2"/></svg><span class="portrait-number"><?= Html::encode($member->contribution['number'] ?? '·') ?></span></div>
                        <div class="member-copy">
                        <h3><?= Html::encode($member->display_name) ?></h3>
                        <p class="team-role"><?= Html::encode($member->role) ?></p>
                        <?= Html::a('了解成员', ['team/member', 'id' => $member->id], [
                            'class' => 'team-detail-link',
                            'aria-label' => '了解' . $member->display_name,
                        ]) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <div class="team-closing"><p>成员姓名暂为占位，分工按项目实际安排展示。</p><?= Html::a('查看项目分工 ↗', ['site/project'], ['class' => 'text-link']) ?></div>
</section>
