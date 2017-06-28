<?php

namespace app\modules\controllers;

use app\modules\models\Rbac;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\data\Pagination;
use app\models\User;
use app\models\Profile;
use Yii;
use app\modules\controllers\CommonController;

class RbacController extends CommonController
{
    public $mustLogin = ['createrole'];
    public $layout    = 'layout1';

    /**
     * 添加角色
     * @return string
     * @throws \Exception
     */
    public function actionReg ()
    {
        if (Yii::$app->request->isPost) {
            $auth = Yii::$app->authManager;
            $role = $auth->createRole(NULL);
            $post = Yii::$app->request->post();
            if (empty($post['name']) || empty($post['description'])) {
                throw new \Exception('参数错误');
            }
            $role->name        = $post['name'];
            $role->description = $post['description'];
            $role->ruleName    = empty($post['rule_name']) ? NULL : $post['rule_name'];
            $role->data        = empty($post['data']) ? NULL : $post['data'];
            if ($auth->add($role)) {
                Yii::$app->session->setFlash('info', '添加成功');
            }
        }

        return $this->render('reg', ['model' => $role]);
    }

    /**
     * 角色列表
     * @return string
     */
    public function actionRoles()
    {
        $auth = Yii::$app->authManager;
        $data = new ActiveDataProvider([
            'query' => (new Query)->from($auth->itemTable)->where('type = 1')->orderBy('created_at desc'),
            'pagination' => ['pageSize' => 5],
        ]);
        return $this->render('_items', ['dataProvider' => $data]);
    }

    /**
     * 分配权限
     * @param $name
     * @return string
     */
    public function actionAssignitem($name)
    {
        //防止注入
        $name = htmlspecialchars($name);
        $auth = Yii::$app->authManager;
        $parent = $auth->getRole($name);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (Rbac::addChild($post['children'], $name)) {
                Yii::$app->session->setFlash('info', '分配成功');
            }
        }

        $children = Rbac::getChildrenByName($name);
        $roles = Rbac::getOptions($auth->getRoles(), $parent);
        $permissions = Rbac::getOptions($auth->getPermissions(), $parent);

        return $this->render('_assignitem', ['parent' => $name, 'roles' => $roles, 'permissions' => $permissions, 'children' => $children]);
    }




}
