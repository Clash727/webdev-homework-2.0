<?php
/**
 * 用途：事务初始化课设演示团队，重复运行保留已有资料。
 * 作者：zhixiu-feng
 */

namespace app\commands;

use app\models\MemberLink;
use app\models\ProjectMember;
use app\models\ProjectTeam;
use Yii;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class TeamSeedController extends Controller
{
    /** php yii team-seed；不覆盖后续后台编辑的资料。 */
    public function actionIndex(): int
    {
        if (ProjectTeam::findPublic()->exists()) {
            $this->stdout("团队已存在，保留全部资料。\n");
            return 0;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $team = new ProjectTeam([
                'slug' => ProjectTeam::PUBLIC_SLUG,
                'name' => '课设演示团队',
                'summary' => "围绕世界杯专题站，协作完成赛事浏览、团队展示与内容维护。\n以下为演示成员资料。",
            ]);
            $this->saveRecord($team);

            $a = new ProjectMember([
                'project_team_id' => $team->id,
                'display_name' => '演示成员 A',
                'role' => '团队展示与资料维护',
                'bio' => '负责团队主页、成员资料与公开链接，让团队的分工和成果清晰可见。',
                'sort_order' => 10,
            ]);
            $this->saveRecord($a);
            $this->saveRecord(new ProjectMember([
                'project_team_id' => $team->id,
                'display_name' => '演示成员 B',
                'role' => '赛程与赛果展示',
                'bio' => '负责比赛信息的组织与呈现，关注赛程、比分和公开状态的一致性。',
                'sort_order' => 20,
            ]));
            $this->saveRecord(new MemberLink([
                'project_member_id' => $a->id,
                'title' => '项目仓库',
                'url' => 'https://github.com/kleedaisuki/webdev-homework-2.0',
                'sort_order' => 0,
            ]));
            $transaction->commit();
            $this->stdout("已创建演示团队、2 位演示成员与 1 条公开链接。\n");
            return 0;
        } catch (\Throwable $error) {
            $transaction->rollBack();
            $this->stderr("初始化失败，未保存部分资料：" . $error->getMessage() . "\n", Console::FG_RED);
            return 1;
        }
    }

    private function saveRecord(ActiveRecord $record): void
    {
        if (!$record->save()) {
            throw new \RuntimeException(implode('；', $record->getFirstErrors()));
        }
    }
}
