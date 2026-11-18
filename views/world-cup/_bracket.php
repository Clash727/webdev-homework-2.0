<?php

use app\models\BracketLayout;
use app\models\Fixture;
use yii\helpers\Html;

$layout = BracketLayout::arrange($matches);
?>
<nav class="bracket-jump" aria-label="跳到轮次">
    <?php foreach ($layout['columns'] as $stage => $round): ?>
        <?= Html::a(Fixture::KNOCKOUT_ROUNDS[$stage], '#' . ($round === [] ? 'round-' : 'first-') . $stage) ?>
    <?php endforeach; ?>
</nav>
<div class="bracket-scroll" tabindex="0" aria-label="淘汰赛晋级树，可横向滚动">
    <div class="bracket-board">
        <div class="bracket-headings">
            <?php foreach ($layout['columns'] as $stage => $round): ?>
                <h2 id="round-<?= $stage ?>"><?= $round === [] ? Fixture::KNOCKOUT_ROUNDS[$stage] : Html::a(Fixture::KNOCKOUT_ROUNDS[$stage], '#first-' . $stage) ?></h2>
            <?php endforeach; ?>
        </div>
        <div class="bracket-body" style="--bracket-height: <?= $layout['height'] ?>px">
            <svg class="bracket-lines" viewBox="0 0 1000 <?= $layout['height'] ?>" preserveAspectRatio="none" aria-hidden="true">
                <?php foreach ($layout['connections'] as $path): ?>
                    <path d="<?= $path ?>" vector-effect="non-scaling-stroke" />
                <?php endforeach; ?>
            </svg>
            <?php foreach ($layout['columns'] as $stage => $round): ?>
                <div class="bracket-round" role="group" aria-labelledby="round-<?= $stage ?>">
                    <?php if ($round === []): ?><p class="bracket-pending">待确认</p><?php endif; ?>
                    <?php foreach ($round as $index => $match): ?>
                        <div class="bracket-node"<?= $index === 0 ? ' id="first-' . $stage . '"' : '' ?> style="--bracket-center: <?= $layout['positions'][$match['key']] ?>px">
                            <?= $this->render('_bracket-match', ['match' => $match, 'nodes' => $layout['nodes']]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php if ($layout['thirdPlace'] !== []): ?>
    <section class="bronze-round">
        <h2>季军赛</h2>
        <?php foreach ($layout['thirdPlace'] as $match): ?>
            <?= $this->render('_bracket-match', ['match' => $match, 'nodes' => $layout['nodes']]) ?>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
