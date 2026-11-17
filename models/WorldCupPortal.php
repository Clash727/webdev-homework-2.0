<?php
/**
 * 用途：复用已发布赛事，提供首页及世界杯专题的只读数据。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\db\ActiveQuery;

class WorldCupPortal
{
    public static function matches(): ActiveQuery
    {
        return Fixture::findPublished()->alias('f')
            ->innerJoin(['t' => Tournament::tableName()], 't.id = f.tournament_id')
            ->andWhere(['t.edition' => Tournament::DEFAULT_EDITION])
            ->with(['homeTeam', 'awayTeam', 'venue']);
    }

    public static function recentResults(int $limit = 3): array
    {
        return self::matches()->andWhere(['f.match_status' => Fixture::STATUS_FINISHED])
            ->orderBy(['f.score_updated_at' => SORT_DESC, 'f.id' => SORT_DESC])->limit($limit)->all();
    }

    public static function highlights(): array
    {
        // 原有视觉方案已收录的 FIFA 观看链接；不下载或冒充站内视频。
        return [
            ['title' => '韩国 × 捷克', 'label' => '小组赛 · A 组', 'codes' => ['KOR', 'CZE'], 'theme' => 'orange',
                'url' => 'https://www.fifa.com/en/watch/1iidGe97khg8lmdSRopdh4'],
            ['title' => '美国 × 巴拉圭', 'label' => '小组赛 · D 组', 'codes' => ['USA', 'PAR'], 'theme' => 'green',
                'url' => 'https://www.fifa.com/en/watch/6jzgitUqP6YyXpwwuY6VRc'],
            ['title' => '更多世界杯影像', 'label' => 'FIFA 官方视频', 'codes' => ['20', '26'], 'theme' => 'cream',
                'url' => 'https://www.fifa.com/en/watch'],
        ];
    }

    public static function discussion(): array
    {
        return [
            'messages' => GuestMessage::findApproved()->select(['nickname', 'body', 'created_at'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->limit(6)->asArray()->all(),
            'canSubmit' => true,
        ];
    }
}
