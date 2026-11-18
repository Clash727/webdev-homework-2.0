<?php

use yii\helpers\Html;

$outcomes = ['win' => '胜', 'draw' => '平', 'loss' => '负'];
$reverse = ['win' => 'loss', 'draw' => 'draw', 'loss' => 'win'];
?>
<div class="group-legend" aria-label="赛果图例">
    <?php foreach ($outcomes as $key => $label): ?>
        <span class="is-<?= $key ?>"><?= $label ?></span>
    <?php endforeach; ?>
</div>
<div class="group-grid">
    <?php foreach ($matches as $group): ?>
        <?php
        $teams = $pairs = [];
        $heading = $group['name'] === null ? '未分组' : $group['name'] . ' 组';
        foreach ($group['matches'] as $match) {
            $home = $match['home']['code'];
            $away = $match['away']['code'];
            $teams[$home] = $match['home']['name'];
            $teams[$away] = $match['away']['name'];
            $score = $match['score'];
            $outcome = $match['homeOutcome'];
            $pairs[$home][$away][] = [$match['detailUrl'], $score === null ? '—' : $score['home'] . '–' . $score['away'], $outcome];
            $pairs[$away][$home][] = [$match['detailUrl'], $score === null ? '—' : $score['away'] . '–' . $score['home'], $outcome === null ? null : $reverse[$outcome]];
        }
        ksort($teams);
        ?>
        <section class="group-board">
            <h2><?= Html::encode($heading) ?></h2>
            <table class="group-matrix" aria-label="<?= Html::encode($heading . '对阵') ?>">
                <thead><tr>
                    <td></td>
                    <?php foreach ($teams as $code => $team): ?>
                        <th scope="col" title="<?= Html::encode($team) ?>"><?= Html::encode($code) ?></th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                <?php foreach ($teams as $row => $team): ?>
                    <tr>
                        <th scope="row" title="<?= Html::encode($team) ?>"><?= Html::encode($row) ?></th>
                        <?php foreach ($teams as $column => $opponent): ?>
                            <td<?= $row === $column ? ' class="is-diagonal"' : '' ?>>
                                <?php foreach ($pairs[$row][$column] ?? [] as [$url, $scoreText, $outcome]): ?>
                                    <?= Html::a(Html::encode($scoreText), $url, [
                                        'class' => 'matrix-result is-' . ($outcome ?? 'scheduled'),
                                        'aria-label' => $team . ' 对 ' . $opponent . '：' . ($outcome === null ? '未开赛' : $scoreText . '，' . $outcomes[$outcome]),
                                    ]) ?>
                                <?php endforeach; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</div>
