<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Category;
use app\models\Cart;
use app\models\User;
use app\models\Product;
use Yii;
use yii\filters\AccessControl;
use \yii\filters\VerbFilter;

class CommonController extends Controller
{
    protected $actions   = ['*'];
    protected $except    = [];
    protected $mustlogin = [];
    protected $verbs     = ['get', 'post'];

    /**
     * @return array
     */
    public function behaviors ()
    {
        return [
            /*            'access' => [
                            'class'  => AccessControl::className(),
                            'only'   => $this->actions,
                            'except' => $this->except,
                            'rules'  => [
                                [
                                    'allow'   => false,
                                    'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
                                    'roles'   => ['?'], // guest
                                ],
                                [
                                    'allow'   => true,
                                    'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
                                    'roles'   => ['@'],
                                ],
                            ],
                        ],*/
            /*            //访问过滤 get post
                        'verbs'  => [
                            'class'   => VerbFilter::className(),
                            'actions' => $this->verbs,
                        ],*/
        ];
    }


    public function init ()
    {
        $cache = Yii::$app->cache;
        $key   = 'menus';
        if (!$menu = $cache->get($key)) {
            $menu = Category::getMenu();
            $cache->set($key, $menu, 3600 * 2);
        }
        $this->view->params['menu'] = $menu;
        $key                        = "cart";
        if (!$data = $cache->get($key)) {


            $data             = [];
            $data['products'] = [];
            $total            = 0;
            if (!Yii::$app->user->isGuest) {
                $usermodel = Yii::$app->user->getIdentity();
                if (!empty($usermodel) && !empty($usermodel->userid)) {
                    $userid = $usermodel->userid;
                    $carts  = Cart::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
                    foreach ($carts as $k => $pro) {
                        $product                            = Product::find()->where('productid = :pid', [':pid' => $pro['productid']])->one();
                        $data['products'][$k]['cover']      = $product->cover;
                        $data['products'][$k]['title']      = $product->title;
                        $data['products'][$k]['productnum'] = $pro['productnum'];
                        $data['products'][$k]['price']      = $pro['price'];
                        $data['products'][$k]['productid']  = $pro['productid'];
                        $data['products'][$k]['cartid']     = $pro['cartid'];
                        $total                              += $data['products'][$k]['price'] * $data['products'][$k]['productnum'];
                    }
                }
            }
            //设置数据库依赖 当添加购物车的时候更新缓存
            $dep = new  \yii\caching\DbDependency([
                'sql'    => 'select max(updatetime) from {{%cart}} where userid = :uid',
                'params' => [':uid' => Yii::$app->user->id],

            ]);
            $cache->set($key, $data, 60, $dep);
            $data['total'] = $total;

        }
        $this->view->params['cart'] = $data;
        $tui                        = Product::find()->where('istui = "1" and ison = "1"')->orderby('createtime desc')->limit(3)->all();
        $new                        = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(3)->all();
        $hot                        = Product::find()->where('ison = "1" and ishot = "1"')->orderby('createtime desc')->limit(3)->all();
        $sale                       = Product::find()->where('ison = "1" and issale = "1"')->orderby('createtime desc')->limit(3)->all();
        $this->view->params['tui']  = (array)$tui;
        $this->view->params['new']  = (array)$new;
        $this->view->params['hot']  = (array)$hot;
        $this->view->params['sale'] = (array)$sale;
    }
}
