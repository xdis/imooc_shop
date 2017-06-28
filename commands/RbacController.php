<?php

namespace app\commands;

use yii\console\Controller;
use yii\db\Exception;


class  RbacController extends controller
{

    public function init ()
    {
        $trans = \Yii::$app->db->beginTransaction();
        try {
            $dir         = dirname(dirname(__FILE__)) . '/modules/controllers';
            $controllers = glob($dir . '/*');
            $permissions = [];
            foreach ($controllers as $controller) {
                $content = file_get_contents($controller);


                /*          $controllerName = preg_replace_callback('/class ([a-zA-Z]+)Controller/', function ($match) {
                              return $match[1];
                          }, $content);*/

                //得到控制器名称
                preg_match('/class ([a-zA-Z]+)Controller/', $content, $matchController);
                $controllerName = $matchController[1];
                $permissions[]  = strtolower($controllerName . '/*');
                //得到方法名称
                preg_match_all('/public function action([a-zA-Z_]+)/', $content, $matchFunction);
                foreach ($matchFunction[1] as $functionName) {
                    $permissions[] = strtolower($controllerName . '/' . $functionName);
                }
            }

            $auth = \Yii::$app->authManager;
            foreach ($permissions as $permission) {
                //查询数据库是否存在
                if (!$auth->getPermission($permission)) {
                    $obj              = $auth->createPermission($permission);
                    $obj->description = $permission;
                    $auth->add($obj);
                }
            }
            $trans->commit();
            echo "import success";
        } catch (Exception $e) {
            $trans->rollback();
            echo "import f";
        }
    }
}