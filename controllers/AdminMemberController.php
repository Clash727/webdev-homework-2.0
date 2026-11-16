<?php
/**
 * 用途：管理员编辑本课设团队的成员资料及个人链接。
 * 作者：zhixiu-feng
 */

namespace app\controllers;

use app\models\ProfileEditor;
use app\models\ProjectMember;
use app\models\ProjectTeam;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class AdminMemberController extends AdminBaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), ['verbs' => [
            'class' => VerbFilter::class, 'actions' => ['update' => ['GET', 'POST']],
        ]]);
    }

    public function actionUpdate($id)
    {
        if ((!is_string($id) && !is_int($id)) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string) $id)
            || (float) $id > 4294967295) {
            throw new NotFoundHttpException('没有找到这位成员。');
        }
        $member = ProjectMember::find()->alias('m')
            ->innerJoin(['t' => ProjectTeam::tableName()], 't.id=m.project_team_id')
            ->where(['m.id' => $id, 't.slug' => ProjectTeam::PUBLIC_SLUG])->one();
        if ($member === null) { throw new NotFoundHttpException('没有找到这位成员。'); }
        $editor = new ProfileEditor($member);
        if (Yii::$app->request->isPost) {
            $editor->loadSubmission(Yii::$app->request->post($member->formName(), []), Yii::$app->request->post('Links', []));
            $intent = Yii::$app->request->post('intent', 'save');
            if ($intent === 'add-link') {
                $editor->appendRow();
            } elseif ($intent !== 'save') {
                throw new BadRequestHttpException('未知的编辑操作。');
            } elseif ($editor->save()) {
                Yii::$app->session->setFlash('profileSaved', '成员资料已保存。');
                return $this->redirect(['admin-member/update', 'id' => $member->id], 303);
            }
        }
        return $this->render('update', ['member' => $member, 'editor' => $editor]);
    }
}
