<?php
/**
 * 用途：为 W05、W10–W12 后台业务控制器提供共享管理员访问规则。
 * 作者：Clash727（W05）
 */

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

abstract class AdminBaseController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }
}
