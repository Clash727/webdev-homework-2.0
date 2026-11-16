<?php
/**
 * 用途：世界杯专题下的官方集锦与球迷讨论入口。
 * 作者：zhixiu-feng
 */

namespace app\controllers;

use app\models\WorldCupPortal;
use yii\filters\VerbFilter;
use yii\web\Controller;

class WorldCupHubController extends Controller
{
    public function behaviors()
    {
        return ['verbs' => ['class' => VerbFilter::class,
            'actions' => ['highlights' => ['GET'], 'fans' => ['GET']]]];
    }

    public function actionHighlights()
    {
        return $this->render('highlights', ['highlights' => WorldCupPortal::highlights()]);
    }

    public function actionFans()
    {
        return $this->render('fans', WorldCupPortal::discussion());
    }
}
