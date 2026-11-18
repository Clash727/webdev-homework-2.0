<?php

namespace app\models;

use yii\helpers\Url;

final class WorldCupDraw
{
    public static function data(): array
    {
        $fixtures = WorldCupPortal::matches()
            ->orderBy(['f.kickoff_at' => SORT_ASC, 'f.id' => SORT_ASC])->all();
        return [
            'edition' => Tournament::DEFAULT_EDITION,
            'official' => self::collect($fixtures, false),
            'demo' => self::collect($fixtures, true),
        ];
    }

    private static function collect(array $fixtures, bool $demo): array
    {
        $groups = $nodes = $knockout = [];
        foreach ($fixtures as $fixture) {
            if ($fixture->isDemo() === $demo && isset(Fixture::KNOCKOUT_ROUNDS[$fixture->stage])) {
                $nodes[$fixture->sample_key] = $fixture->stage;
            }
        }
        foreach ($fixtures as $fixture) {
            if ($fixture->isDemo() !== $demo || ($fixture->stage !== 'group' && !isset($nodes[$fixture->sample_key]))) {
                continue;
            }
            $score = $fixture->isFinished() ? ['home' => (int) $fixture->home_score, 'away' => (int) $fixture->away_score] : null;
            $match = [
                'key' => $fixture->sample_key,
                'stage' => $fixture->stage,
                'group' => $fixture->group_name,
                'kickoffAt' => (new \DateTimeImmutable($fixture->kickoff_at, new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z'),
                'status' => $fixture->match_status,
                'score' => $score,
                'home' => self::team($fixture->homeTeam),
                'away' => self::team($fixture->awayTeam),
                'detailUrl' => Url::to(['/world-cup/view', 'id' => $fixture->id]),
            ];
            if ($fixture->stage === 'group') {
                $group = $fixture->group_name ?? '';
                $match['homeOutcome'] = $score === null ? null : ['loss', 'draw', 'win'][1 + ($score['home'] <=> $score['away'])];
                $groups[$group][] = $match;
                continue;
            }
            $match['winnerSide'] = $score === null ? null : $fixture->winner_side;
            $match['decidedBy'] = $fixture->decided_by;
            $match['penalties'] = $fixture->home_penalties === null ? null : [
                'home' => (int) $fixture->home_penalties, 'away' => (int) $fixture->away_penalties,
            ];
            $match['homeSource'] = self::source($fixture->home_source, $nodes);
            $match['awaySource'] = self::source($fixture->away_source, $nodes);
            $knockout[] = $match;
        }

        ksort($groups, SORT_NATURAL);
        $groupList = [];
        foreach ($groups as $group => $matches) {
            $groupList[] = ['name' => $group === '' ? null : (string) $group, 'matches' => $matches];
        }
        return ['groups' => $groupList, 'knockout' => $knockout];
    }

    private static function team(NationalTeam $team): array
    {
        return ['code' => $team->code, 'name' => $team->name];
    }

    private static function source(?string $reference, array $publicNodes): ?array
    {
        if ($reference === null) {
            return null;
        }
        [$outcome, $key] = explode(':', $reference, 2);
        return ['nodeId' => isset($publicNodes[$key]) ? $key : null, 'outcome' => $outcome];
    }
}
