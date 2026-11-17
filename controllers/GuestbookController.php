<?php
/** 用途：公开留言列表、CSRF 提交与待审回执。作者：Clash727（W08） */
namespace app\controllers;

use app\models\GuestMessage;
use Yii;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class GuestbookController extends Controller
{
    public function behaviors(): array
    {
        return ['verbs' => [
            'class' => VerbFilter::class,
            'actions' => ['index' => ['GET'], 'create' => ['POST']],
        ]];
    }

    public function actionIndex(): string
    {
        return $this->renderIndex(new GuestMessage());
    }

    public function actionCreate(): string|Response
    {
        $model = new GuestMessage();
        $model->load(Yii::$app->request->post());
        if ($model->save()) {
            Yii::$app->session->setFlash('guestbookSubmitted', '留言已提交，待审核。');
            return $this->redirect(['guestbook/index'], 303);
        }
        return $this->renderIndex($model);
    }

    private function renderIndex(GuestMessage $model): string
    {
        $page = filter_var(Yii::$app->request->get('page', 1), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        if ($page === false) {
            throw new BadRequestHttpException('页码须为正整数。');
        }
        $query = GuestMessage::findApproved()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 20, 'pageSizeParam' => false,
            'page' => $page - 1, 'route' => 'guestbook/index',
        ]);
        return $this->render('index', [
            'model' => $model,
            'messages' => $query->offset($pagination->offset)->limit($pagination->limit)->all(),
            'pagination' => $pagination,
        ]);
    }
}
