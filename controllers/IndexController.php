<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Product;

class IndexController extends CommonController
{
    public function actionIndex ()
    {
/*       $topic = \Yii::$app->request->get('topic');
       // \Yii::error('this is error');
        \Yii::$app->kafka->send('this111 12'.rand(0,100000),$topic);*/

        $this->layout = "layout1";
        $data['tui']  = Product::find()->where('istui = "1" and ison = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['new']  = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['hot']  = Product::find()->where('ison = "1" and ishot = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['all']  = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(7)->all();

        return $this->render("index", ['data' => $data]);
    }
/*    public function actionTest(){
        \Yii::$app->asyncLog->consumer();
        die;
    }

    public function actionError ()
    {

        \Yii::$app->kafka->consumer();
        echo 123123;
        die;
        echo "页面不存在";
    }*/
}
