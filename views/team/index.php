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
                    <li class="team-card">
                        <h3><?= Html::encode($member->display_name) ?></h3>
                        <p class="team-role"><?= Html::encode($member->role) ?></p>
                        <?= Html::a('了解成员', ['team/member', 'id' => $member->id], [
                            'class' => 'team-detail-link',
                            'aria-label' => '了解' . $member->display_name,
                        ]) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</section>
