<?php
/**
 * 用途：公共布局，提供已实现的首页、后台及会话退出入口。
 * 作者：Clash727（W01、W05）
 */

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Html::encode(Yii::$app->language) ?>">
<head>
    <meta charset="<?= Html::encode(Yii::$app->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title . ' · ' . Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<a class="skip-link" href="#main-content">跳到正文</a>
<header class="site-header">
    <a class="brand" href="<?= Html::encode(Url::to(['site/index'])) ?>">世界杯专题站</a>
    <nav class="site-nav" aria-label="主导航">
        <span class="edition">2026</span>
        <?php if (Yii::$app->user->isGuest): ?>
            <?= Html::a('管理员登录', ['auth/login']) ?>
        <?php else: ?>
            <?= Html::a('管理工作台', ['admin/index']) ?>
            <?= Html::beginForm(['auth/logout'], 'post', ['class' => 'logout-form']) ?>
                <?= Html::submitButton('退出登录', ['class' => 'button-link']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </nav>
</header>
<main id="main-content" class="container">
    <?= $content ?>
</main>
<footer class="site-footer">世界杯精选小组赛专题 · 课程团队项目</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
