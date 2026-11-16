<?php
/**
 * 用途：管理员编辑当前课设团队及资源链接。
 * 作者：zhixiu-feng
 */

namespace app\controllers;

use app\models\ProfileEditor;
use app\models\ProjectTeam;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class AdminTeamController extends AdminBaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), ['verbs' => [
            'class' => VerbFilter::class, 'actions' => ['update' => ['GET', 'POST']],
        ]]);
    }

    public function actionUpdate()
    {
        $team = ProjectTeam::findPublic()->with('members')->one();
        if ($team === null) {
            throw new NotFoundHttpException('团队资料尚未初始化，请先运行团队初始化命令。');
        }
        $editor = new ProfileEditor($team);
        if (Yii::$app->request->isPost) {
            $editor->loadSubmission(Yii::$app->request->post($team->formName(), []), Yii::$app->request->post('Links', []));
            $intent = Yii::$app->request->post('intent', 'save');
            if ($intent === 'add-link') {
                $editor->appendRow();
            } elseif ($intent !== 'save') {
                throw new BadRequestHttpException('未知的编辑操作。');
            } elseif ($editor->save()) {
                Yii::$app->session->setFlash('profileSaved', '团队资料已保存。');
                return $this->redirect(['admin-team/update'], 303);
            }
        }
        return $this->render('update', ['team' => $team, 'editor' => $editor]);
    }
}
