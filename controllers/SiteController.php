<?php
/**
 * 用途：世界杯首页、项目分工展示与统一错误页。
 * 作者：Clash727（工程基线）、zhixiu-feng（前台展示）
 */

namespace app\controllers;

use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use app\models\ProjectTeam;
use app\models\WorldCupPortal;

class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['GET'], 'project' => ['GET']],
            ],
        ];
    }

    public function actions(): array
    {
        return ['error' => ['class' => ErrorAction::class]];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'results' => WorldCupPortal::recentResults(),
            'team' => ProjectTeam::findPublic()->with('members')->one(),
        ]);
    }

    public function actionProject(): string
    {
        return $this->render('project', [
            'team' => ProjectTeam::findPublic()->with('members')->one(),
            'portfolio' => require dirname(__DIR__) . '/config/portfolio.php',
        ]);
    }
}
