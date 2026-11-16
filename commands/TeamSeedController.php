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
                'name' => '我们的团队',
                'summary' => '四个人，一座看台。我们把数据、设计与代码连接起来，为 2026 世界杯留下属于自己的项目记录。',
            ]);
            $this->saveRecord($team);

            $this->fillDemoMembers($team);
            $transaction->commit();
            $this->stdout("已创建团队、4 位占位成员与项目链接。\n");
            return 0;
        } catch (\Throwable $error) {
            $transaction->rollBack();
            $this->stderr("初始化失败，未保存部分资料：" . $error->getMessage() . "\n", Console::FG_RED);
            return 1;
        }
    }

    /** 显式升级原始两人演示种子；不覆盖实际维护过的资料。 */
    public function actionCompleteDemo(): int
    {
        $team = ProjectTeam::findPublic()->with('members')->one();
        if ($team === null) {
            return $this->actionIndex();
        }
        if (count($team->members) === 4 && array_filter($team->members, static function ($member) {
            return $member->contribution_key === null;
        }) === []) {
            $this->stdout("四人资料已存在，保留全部内容。\n");
            return 0;
        }
        $original = [
            '演示成员 A' => ['member-2', '团队展示与资料维护', '负责团队主页、成员资料与公开链接，让团队的分工和成果清晰可见。'],
            '演示成员 B' => ['member-3', '赛程与赛果展示', '负责比赛信息的组织与呈现，关注赛程、比分和公开状态的一致性。'],
        ];
        if ($team->name !== '课设演示团队' || count($team->members) !== 2
            || $team->summary !== "围绕世界杯专题站，协作完成赛事浏览、团队展示与内容维护。\n以下为演示成员资料。") {
            $this->stderr("现有资料不是原始演示种子，未改动。\n");
            return 1;
        }
        foreach ($team->members as $member) {
            $expected = $original[$member->display_name] ?? null;
            if ($expected === null || $member->role !== $expected[1] || $member->bio !== $expected[2]
                || $member->contribution_key !== null) {
                $this->stderr("成员资料已维护，未改动。\n");
                return 1;
            }
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($team->members as $member) {
                $member->contribution_key = $original[$member->display_name][0];
                $profile = $member->contribution;
                $member->display_name = '成员' . ['01' => '一', '02' => '二', '03' => '三', '04' => '四'][$profile['number']];
                $member->role = $profile['role'];
                $member->bio = $profile['summary'];
                $member->sort_order = (int) $profile['number'] * 10;
                $this->saveRecord($member);
            }
            $team->name = '我们的团队';
            $team->summary = '四个人，一座看台。我们把数据、设计与代码连接起来，为 2026 世界杯留下属于自己的项目记录。';
            $this->saveRecord($team);
            $this->fillDemoMembers($team);
            $transaction->commit();
            $this->stdout("原始演示资料已升级为四人；已有成员 ID 与链接保留。\n");
            return 0;
        } catch (\Throwable $error) {
            $transaction->rollBack();
            $this->stderr("升级已回滚：" . $error->getMessage() . "\n");
            return 1;
        }
    }

    private function fillDemoMembers(ProjectTeam $team): void
    {
        $names = ['member-1' => '成员一', 'member-2' => '成员二', 'member-3' => '成员三', 'member-4' => '成员四'];
        foreach (require dirname(__DIR__) . '/config/portfolio.php' as $key => $profile) {
            $member = ProjectMember::findOne(['project_team_id' => $team->id, 'contribution_key' => $key]);
            if ($member === null) {
                $member = new ProjectMember(['project_team_id' => $team->id, 'contribution_key' => $key,
                    'display_name' => $names[$key], 'role' => $profile['role'], 'bio' => $profile['summary'],
                    'sort_order' => (int) $profile['number'] * 10]);
                $this->saveRecord($member);
            }
            if ($key === 'member-2' && !$member->getLinks()->exists()) {
                $this->saveRecord(new MemberLink(['project_member_id' => $member->id, 'title' => '项目仓库',
                    'url' => 'https://github.com/kleedaisuki/webdev-homework-2.0', 'sort_order' => 0]));
            }
        }
    }

    private function saveRecord(ActiveRecord $record): void
    {
        if (!$record->save()) {
            throw new \RuntimeException(implode('；', $record->getFirstErrors()));
        }
    }
}
