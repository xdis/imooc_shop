<?php

namespace app\modules\controllers;

use yii\web\Controller;
use app\modules\models\Admin;
use Yii;

class PublicController extends Controller
{
    /**
     * 后台管理员登陆
     * @return string
     */
    public function actionLogin ()
    {
        $this->layout = false;
        $model        = new Admin;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->login($post)) {
                $this->redirect(['default/index']);
                Yii::$app->end();
            }
        }

        return $this->render("login", ['model' => $model]);
    }

    /**
     * 退出登录
     */
    public function actionLogout ()
    {
        //fasle只清除当前session
        Yii::$app->admin->logout(false);
        return $this->redirect(['public/login']);
        /*        Yii::$app->session->removeAll();
              if (!isset(Yii::$app->session['admin']['isLogin'])) {
                   $this->redirect(['public/login']);
                   Yii::$app->end();
               }
               $this->goback();*/
    }

    /**
     * 管理员找回密码
     * @return string
     */
    public function actionSeekpassword ()
    {
        $this->layout = false;
        $model        = new Admin;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->seekPass($post)) {
                Yii::$app->session->setFlash('info', '电子邮件已经发送成功，请查收');
            }
        }

        return $this->render("seekpassword", ['model' => $model]);
    }


}
