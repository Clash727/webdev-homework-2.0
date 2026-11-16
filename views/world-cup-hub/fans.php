<?php
/** 用途：公开已审核的球迷留言，并在原留言功能可用时连接提交入口。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '球迷讨论';
?>
<section class="cup-page"><header class="page-heading"><h1>球迷讨论</h1></header>
<?= $this->render('../shared/_cup-tabs') ?>
<div class="fans-layout"><aside class="fans-intro"><span class="quote-symbol" aria-hidden="true">“</span><?php if ($canSubmit): ?><?= Html::a('写留言 ↗', ['guestbook/index'], ['class' => 'primary-link']) ?><?php else: ?><span class="opening-note">讨论区即将开放</span><?php endif; ?></aside>
<div class="fan-messages">
    <?php if ($messages === []): ?><div class="fans-empty"><h2>暂无留言</h2></div><?php endif; ?>
    <?php foreach ($messages as $message): ?><article class="fan-message"><header><strong><?= Html::encode($message['nickname']) ?></strong><time><?= Html::encode(substr($message['created_at'], 0, 10)) ?></time></header><p><?= nl2br(Html::encode($message['body']), false) ?></p></article><?php endforeach; ?>
</div></div></section>
