<?php

namespace app\modules\controllers;

use app\modules\models\Rbac;
use yii\web\Controller;
use Yii;
use app\modules\models\Admin;
use yii\data\Pagination;
use app\modules\controllers\CommonController;

class ManageController extends CommonController
{

    /**
     * 根据邮箱找回密码
     * @return string
     */
    public function actionMailchangepass ()
    {
        $this->layout = false;
        $time         = Yii::$app->request->get("tamp");
        $adminuser    = Yii::$app->request->get("adminuser");
        $token        = Yii::$app->request->get("token");
        $model        = new Admin;
        $myToken      = $model->createToken($adminuser, $time);


        if ($token != $myToken) {
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        if (time() - $time > 300) {
            $this->redirect(['public/login']);
            Yii::$app->end();
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->changePass($post)) {
                Yii::$app->session->setFlash('info', '密码修改成功');
            }
        }
        $model->adminuser = $adminuser;

        return $this->render("mailchangepass", ['model' => $model]);

    }

    /**
     * 管理员列表
     * @return string
     */
    public function actionManagers ()
    {
        $this->layout = "layout1";
        $model        = Admin::find();
        $count        = $model->count();
        $pageSize     = Yii::$app->params['pageSize']['manage'];
        $pager        = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $managers     = $model->offset($pager->offset)->limit($pager->limit)->all();

        return $this->render("managers", ['managers' => $managers, 'pager' => $pager]);
    }

    /**
     * 添加管理员
     * @return string
     */
    public function actionReg ()
    {
        $this->layout = 'layout1';
        $model        = new Admin;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->reg($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            } else {
                Yii::$app->session->setFlash('info', '添加失败');
            }
        }
        $model->adminpass = '';
        $model->repass    = '';

        return $this->render('reg', ['model' => $model]);
    }

    /**
     * 给用户分配权限
     * @param $adminid
     * @return string
     * @throws \Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionAssign($adminid)
    {
        $this->layout = 'layout1';
        $adminid = (int)$adminid;
        if (empty($adminid)) {
            throw new \Exception('参数错误');
        }
        $admin = Admin::findOne($adminid);
        if (empty($admin)) {
            throw new \yii\web\NotFoundHttpException('admin not found');
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $children = !empty($post['children']) ? $post['children'] : [];
            if (Rbac::grant($adminid, $children)) {
                Yii::$app->session->setFlash('info', '授权成功');
            }
        }
        $auth = Yii::$app->authManager;
        $roles = Rbac::getOptions($auth->getRoles(), null);
        $permissions = Rbac::getOptions($auth->getPermissions(), null);
        $children = Rbac::getChildrenByUser($adminid);

        return $this->render('_assign', ['children' => $children, 'roles' => $roles, 'permissions' => $permissions, 'admin' => $admin->adminuser]);
    }

    /**
     * 删除用户
     * @return bool
     */
    public function actionDel ()
    {
        $adminid = (int)Yii::$app->request->get("adminid");
        if (empty($adminid) || $adminid == 1) {
            $this->redirect(['manage/managers']);

            return false;
        }
        $model = new Admin;
        if ($model->deleteAll('adminid = :id', [':id' => $adminid])) {
            Yii::$app->session->setFlash('info', '删除成功');
            $this->redirect(['manage/managers']);
        }
    }

    public function actionChangeemail ()
    {
        $this->layout = 'layout1';
        $model        = Admin::find()->where('adminuser = :user', [':user' => Yii::$app->session['admin']['adminuser']])->one();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->changeemail($post)) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
        }
        $model->adminpass = "";

        return $this->render('changeemail', ['model' => $model]);
    }

    public function actionChangepass ()
    {
        $this->layout = "layout1";
        $model        = Admin::find()->where('adminuser = :user', [':user' => Yii::$app->session['admin']['adminuser']])->one();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->changepass($post)) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
        }
        $model->adminpass = '';
        $model->repass    = '';

        return $this->render('changepass', ['model' => $model]);
    }


}
