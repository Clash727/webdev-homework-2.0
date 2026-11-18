<?php

namespace app\models;

final class BracketLayout
{
    public static function arrange(array $matches): array
    {
        $stages = array_keys(Fixture::KNOCKOUT_ROUNDS);
        $columnIndex = array_flip(array_slice($stages, 0, 5));
        $columns = array_fill_keys(array_keys($columnIndex), []);
        $nodes = [];
        $thirdPlace = [];
        foreach ($matches as $match) {
            $stage = $match['stage'];
            if ($stage === 'third-place') {
                $thirdPlace[] = $match;
                continue;
            }
            $columns[$stage][] = $match;
            $nodes[$match['key']] = $match;
        }

        $positions = $connections = [];
        $nextSlot = 37;
        $place = function (array $node) use (&$place, &$nextSlot, &$positions, &$connections, $nodes, $columnIndex): int {
            $key = $node['key'];
            if (isset($positions[$key])) {
                return $positions[$key];
            }

            $column = $columnIndex[$node['stage']];
            $parents = [];
            foreach (['homeSource', 'awaySource'] as $slot) {
                $previous = $nodes[$node[$slot]['nodeId'] ?? ''] ?? null;
                if ($previous !== null && $columnIndex[$previous['stage']] === $column - 1) {
                    $parents[] = $place($previous);
                }
            }
            if ($parents === []) {
                $center = $nextSlot;
                $nextSlot += 76;
            } else {
                $center = intdiv(array_sum($parents), count($parents));
            }
            $positions[$key] = $center;
            foreach ($parents as $previousCenter) {
                $from = 200 * ($column - 1) + 168;
                $to = 200 * $column;
                $middle = $from + 16;
                $connections[] = "M{$from},{$previousCenter} H{$middle} V{$center} H{$to}";
            }
            return $center;
        };

        foreach (array_reverse($columns) as $matchesInRound) {
            foreach ($matchesInRound as $match) {
                $place($match);
            }
        }
        foreach ($columns as &$matchesInRound) {
            usort($matchesInRound, static fn (array $first, array $second) => $positions[$first['key']] <=> $positions[$second['key']]);
        }
        unset($matchesInRound);

        return compact('columns', 'positions', 'connections', 'thirdPlace', 'nodes') + ['height' => max(76, $nextSlot - 37)];
    }
}
