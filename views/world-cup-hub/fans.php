<?php
/** 用途：公开已审核的球迷留言，并在原留言功能可用时连接提交入口。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '球迷讨论';
?>
<section class="cup-page"><header class="page-heading"><h1>球迷讨论</h1></header>
<?= $this->render('../shared/_cup-tabs') ?>
<div class="fans-layout"><aside class="fans-intro"><span class="quote-symbol" aria-hidden="true">“</span><?= Html::a('写留言 ↗', ['guestbook/index'], ['class' => 'primary-link']) ?></aside>
<div class="fan-messages">
    <?php if ($messages === []): ?><div class="fans-empty"><h2>暂无留言</h2></div><?php endif; ?>
    <?php foreach ($messages as $message): ?><?= $this->render('../shared/_guest-message', ['message' => $message]) ?><?php endforeach; ?>
</div></div></section>
