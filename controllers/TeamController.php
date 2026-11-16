<?php
/**
 * 用途：公开课设团队主页与本团队成员详情。
 * 作者：zhixiu-feng
 */

namespace app\controllers;

use app\models\ProjectMember;
use app\models\ProjectTeam;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class TeamController extends Controller
{
    public function behaviors()
    {
        return ['verbs' => [
            'class' => VerbFilter::class,
            'actions' => ['index' => ['GET'], 'member' => ['GET']],
        ]];
    }

    public function actionIndex()
    {
        $team = ProjectTeam::findPublic()->with(['members', 'resourceLinks'])->one();
        if ($team === null) {
            throw new NotFoundHttpException('团队资料尚未准备好，请联系维护者初始化团队资料。');
        }

        return $this->render('index', ['team' => $team]);
    }

    public function actionMember($id)
    {
        // 不将超长整数强转或截断为其他成员 ID。
        if (!is_string($id) && !is_int($id)) {
            throw new NotFoundHttpException('没有找到这位成员。');
        }
        if (!preg_match('/^[1-9][0-9]{0,9}$/D', (string) $id) || (float) $id > 4294967295) {
            throw new NotFoundHttpException('没有找到这位成员。');
        }

        $member = ProjectMember::find()->alias('member')
            ->innerJoin(['team' => ProjectTeam::tableName()], 'team.id = member.project_team_id')
            ->where(['member.id' => $id, 'team.slug' => ProjectTeam::PUBLIC_SLUG])
            ->with(['team', 'links'])->one();
        if ($member === null) {
            throw new NotFoundHttpException('没有找到这位成员。');
        }

        return $this->render('member', ['member' => $member]);
    }
}
