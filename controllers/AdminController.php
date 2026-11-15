<?php
/**
 * 用途：提供仅管理员可访问的后台首页。
 * 作者：Clash727（W05）
 */

namespace app\controllers;

use yii\filters\VerbFilter;

class AdminController extends AdminBaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['GET']],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
