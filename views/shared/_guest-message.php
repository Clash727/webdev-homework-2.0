<?php
/** 用途：以纯文本展示已审核留言，统一北京时间。作者：Clash727（W08） */
use yii\helpers\Html;
$created = new DateTimeImmutable($message['created_at'], new DateTimeZone('UTC'));
?>
<article class="fan-message">
    <header>
        <strong><?= Html::encode($message['nickname']) ?></strong>
        <time datetime="<?= Html::encode($created->format(DATE_ATOM)) ?>" title="北京时间"><?= Html::encode($created->setTimezone(new DateTimeZone('Asia/Shanghai'))->format('Y-m-d H:i')) ?></time>
    </header>
    <p><?= nl2br(Html::encode($message['body']), false) ?></p>
</article>
