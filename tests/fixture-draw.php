<?php

use app\models\Fixture;
use app\models\Tournament;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');

function drawRequest(string $path): string
{
    global $baseUrl;
    $request = curl_init($baseUrl . $path);
    curl_setopt_array($request, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    $body = curl_exec($request);
    if ($body === false || curl_getinfo($request, CURLINFO_RESPONSE_CODE) !== 200) {
        throw new RuntimeException('GET failed: ' . $path);
    }
    return $body;
}

function drawCheck(bool $passed, string $label): void
{
    if (!$passed) {
        throw new RuntimeException($label);
    }
    echo 'PASS ' . $label . PHP_EOL;
}

$otherEdition = null;
$temporary = [];
try {
    $read = static fn () => json_decode(drawRequest('/world-cup/draw-data'), true, 512, JSON_THROW_ON_ERROR);
    $data = $read();
    drawCheck($data['edition'] === 2026 && array_column($data['official']['groups'], 'name') === range('A', 'L'), '12 official groups');
    drawCheck(array_map(static fn ($group) => count($group['matches']), $data['official']['groups']) === array_fill(0, 12, 6), '6 matches per group');
    $groupMatches = array_column(array_merge(...array_column($data['official']['groups'], 'matches')), null, 'key');
    drawCheck($groupMatches['official-2026-001']['homeOutcome'] === 'win'
        && $groupMatches['official-2026-003']['homeOutcome'] === 'draw', 'group outcome matches official results');

    $knockout = array_column($data['official']['knockout'], null, 'key');
    $roundCounts = array_count_values(array_column($knockout, 'stage'));
    drawCheck(array_values(array_replace(array_fill_keys(array_keys(Fixture::KNOCKOUT_ROUNDS), 0), $roundCounts)) === [16, 8, 4, 2, 1, 1], '32 official matches across six rounds');
    $penalties = $knockout['official-2026-096'];
    drawCheck($penalties['score'] === ['home' => 0, 'away' => 0] && $penalties['decidedBy'] === 'penalties'
        && $penalties['penalties'] === ['home' => 4, 'away' => 3] && $penalties['winnerSide'] === 'home', '0:0 stays separate from the shootout');

    $edges = 0;
    foreach ($knockout as $match) {
        foreach (['home', 'away'] as $side) {
            $source = $match[$side . 'Source'];
            if ($source === null) {
                continue;
            }
            $previous = $knockout[$source['nodeId']];
            $winnerSide = $previous['winnerSide'];
            if ($source['outcome'] === 'loser') {
                $winnerSide = $winnerSide === 'home' ? 'away' : 'home';
            }
            if ($match[$side] !== $previous[$winnerSide]) {
                throw new RuntimeException('Source and team mismatch: ' . $match['key']);
            }
            $edges++;
        }
    }
    drawCheck($edges === 32 && $knockout['official-2026-103']['homeSource']['outcome'] === 'loser'
        && $knockout['official-2026-103']['awaySource']['outcome'] === 'loser', 'all source links resolve to the correct team, including both semifinal losers');
    $final = $knockout['official-2026-104'];
    drawCheck($final['decidedBy'] === 'extra-time' && str_contains(drawRequest($final['detailUrl']), '加时赛'), 'final links to the detail with extra time');

    $existing = Fixture::findOne(['sample_key' => 'official-2026-001']);
    $attributes = $existing->getAttributes(null, ['id']);
    $prefix = 'demo-draw-test-' . bin2hex(random_bytes(5));
    $now = gmdate('Y-m-d H:i:s');
    $otherEdition = new Tournament(['edition' => max(2026, (int) Tournament::find()->max('edition')) + 1, 'name' => $prefix, 'summary' => $prefix]);
    drawCheck($otherEdition->save(), 'temporary separate edition is available');
    foreach (['upcoming', 'draft', 'outside', 'pending-winner'] as $kind) {
        $match = new Fixture(array_replace($attributes, [
            'sample_key' => $prefix . '-' . $kind,
            'tournament_id' => $kind === 'outside' ? $otherEdition->id : $existing->tournament_id,
            'is_demo' => 1,
            'stage' => $kind === 'upcoming' ? 'group' : 'round-of-16',
            'group_name' => $kind === 'upcoming' ? 'X' : null,
            'match_status' => $kind === 'pending-winner' ? 'finished' : 'scheduled',
            'publication_status' => $kind === 'draft' ? 'draft' : 'published',
            'home_score' => $kind === 'pending-winner' ? 0 : null,
            'away_score' => $kind === 'pending-winner' ? 0 : null,
            'score_updated_at' => $kind === 'pending-winner' ? $now : null,
            'published_at' => $kind === 'draft' ? null : $now,
            'home_source' => $kind === 'pending-winner' ? 'winner:' . $prefix . '-draft' : null,
            'away_source' => $kind === 'pending-winner' ? 'winner:official-2026-074' : null,
        ]));
        if (!$match->save()) {
            throw new RuntimeException('Failed to set up ' . $kind . ': ' . json_encode($match->errors));
        }
        $temporary[] = $match->id;
    }
    $data = $read();
    $demoGroups = array_column($data['demo']['groups'], 'matches', 'name');
    $demo = array_column($data['demo']['knockout'], null, 'key');
    $pending = $demo[$prefix . '-pending-winner'];
    drawCheck($demoGroups['X'][0]['score'] === null && $demoGroups['X'][0]['homeOutcome'] === null, 'upcoming demo has no score or outcome');
    drawCheck(!isset($demo[$prefix . '-outside']) && !isset($demo[$prefix . '-draft']) && $pending['homeSource']['nodeId'] === null
        && $pending['awaySource']['nodeId'] === null, 'drafts, different editions, and cross-source links are hidden');
    drawCheck($pending['winnerSide'] === null && str_contains(drawRequest('/world-cup/bracket?source=demo'), '胜者待确认'), 'unconfirmed tied winner is not inferred');
} finally {
    if ($temporary !== []) {
        Fixture::deleteAll(['id' => $temporary]);
    }
    if ($otherEdition !== null && !$otherEdition->isNewRecord) {
        $otherEdition->delete();
    }
}
