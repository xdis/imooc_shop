<?php

namespace app\modules\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class CommonController extends Controller
{
    protected $actions   = ['*'];
    protected $except    = [];
    protected $mustLogin = [];
    protected $verb      = [];

    public function behaviors ()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'user'   => 'admin', //指定后台
                'only'   => $this->actions,
                'except' => $this->except,
                'rules'  => [
                    [
                        'allow'   => false,
                        'actions' => empty($this->mustLogin) ? [] : $this->mustLogin,
                        'roles'   => ['?'], // guest
                    ],

                    [
                        'allow'   => true,
                        'actions' => empty($this->mustLogin) ? [] : $this->mustLogin,
                        'roles'   => ['@'],
                    ],

                ],
            ],
            //访问过滤 get post
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => $this->verb,
            ],
        ];

    }


    public function beforeAction ($action)
    {
        //获取当前用户要访问的控制器名称和控制器

        if (!parent::beforeAction($action)) {
            return false;
        }

        $controller = $action->controller->id;
        $actionName = $action->id;
        if (Yii::$app->admin->can($controller . '/*')) {
            return true;
        }
        if (Yii::$app->admin->can($controller . '/' . $actionName)) {
            return true;
        }

        throw new \yii\web\UnauthorizedHttpException('对不起，您没有访问' . $controller . '/' . $actionName . '的权限');
    }

    public function init ()
    {


        // return true;
    }

    public function actionP ($param)
    {
        echo "<pre>";
        var_dump($param);

    }
}
