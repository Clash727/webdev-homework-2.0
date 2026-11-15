<?php
/**
 * 用途：展示当前管理员身份和后台工作台。
 * 作者：Clash727（W05）
 */

use yii\helpers\Html;

$this->title = '管理工作台';
?>
<section class="admin-page">
    <p class="eyebrow">WORLD CUP 2026 · 后台</p>
    <h1>管理工作台</h1>
    <p class="intro">你好，<?= Html::encode(Yii::$app->user->identity->display_name) ?>。</p>
    <p class="status">已登录</p>
    <p class="note">团队资料、赛事维护和留言审核功能将陆续开放。</p>
</section>
