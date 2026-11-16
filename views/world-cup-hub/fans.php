<?php
/** 用途：公开已审核的球迷留言，并在原留言功能可用时连接提交入口。作者：zhixiu-feng */
use yii\helpers\Html;
$this->title = '球迷讨论';
?>
<section class="cup-page"><header class="page-heading"><p class="eyebrow">2026 世界杯</p><h1>比赛会终场，<br>热爱不会散场。</h1></header>
<?= $this->render('../shared/_cup-tabs') ?>
<div class="fans-layout"><aside class="fans-intro"><span class="quote-symbol" aria-hidden="true">“</span><h2>这里是<br>球迷的看台。</h2><p>为一个进球欢呼，也为一场比赛留下自己的声音。</p><?php if ($canSubmit): ?><?= Html::a('写下你的看球感受 ↗', ['guestbook/index'], ['class' => 'primary-link']) ?><?php else: ?><span class="opening-note">讨论区即将开放</span><?php endif; ?></aside>
<div class="fan-messages">
    <?php if ($messages === []): ?><div class="fans-empty"><span aria-hidden="true">↗</span><h2>等一句，来自看台的声音。</h2><p>这里将展示审核通过的球迷留言。</p></div><?php endif; ?>
    <?php foreach ($messages as $message): ?><article class="fan-message"><header><strong><?= Html::encode($message['nickname']) ?></strong><time><?= Html::encode(substr($message['created_at'], 0, 10)) ?></time></header><p><?= nl2br(Html::encode($message['body']), false) ?></p></article><?php endforeach; ?>
</div></div></section>
