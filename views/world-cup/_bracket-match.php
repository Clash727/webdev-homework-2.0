<?php

use yii\helpers\Html;

$sourceText = [];
$unresolvedSource = false;
foreach (['home', 'away'] as $side) {
    $source = $match[$side . 'Source'];
    if ($source === null) {
        continue;
    }
    $previous = $nodes[$source['nodeId']] ?? null;
    $unresolvedSource = $unresolvedSource || $previous === null;
    $sourceText[] = $match[$side]['name'] . '：' . ($previous === null ? '上轮待确认' : (
        $previous['home']['name'] . ' 对 ' . $previous['away']['name'] . '的' . ($source['outcome'] === 'loser' ? '负方' : '胜方')
    ));
}
$label = $match['home']['name'] . ' 对 ' . $match['away']['name'];
$label .= $match['score'] === null ? '，未开赛' : '，' . implode(' 比 ', $match['score']);
$label .= match ($match['decidedBy']) {
    'penalties' => '，点球 ' . implode(' 比 ', $match['penalties']),
    'extra-time' => '，加时',
    default => '',
};
if ($match['winnerSide'] !== null) {
    $label .= '，' . $match[$match['winnerSide']]['name'] . '胜出';
} elseif ($match['score'] !== null) {
    $label .= '，胜者待确认';
}
if ($sourceText !== []) {
    $label .= '。' . implode('；', $sourceText);
}
?>
<?= Html::beginTag('a', ['href' => $match['detailUrl'], 'class' => 'bracket-match', 'aria-label' => $label, 'title' => $label]) ?>
    <?php foreach (['home', 'away'] as $side): ?>
        <span class="bracket-team<?= $match['winnerSide'] === $side ? ' is-winner' : '' ?>">
            <span><?= Html::encode($match[$side]['code']) ?></span>
            <span class="bracket-score"><?= $match['score'][$side] ?? '—' ?></span>
        </span>
    <?php endforeach; ?>
    <?php if ($match['decidedBy'] === 'penalties'): ?>
        <small>点球 <?= implode('–', $match['penalties']) ?></small>
    <?php elseif ($match['decidedBy'] === 'extra-time'): ?>
        <small>加时</small>
    <?php elseif ($match['winnerSide'] === null && $match['score'] !== null): ?>
        <small>胜者待确认</small>
    <?php elseif ($unresolvedSource): ?>
        <small>上轮待确认</small>
    <?php endif; ?>
<?= Html::endTag('a') ?>
