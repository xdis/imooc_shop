<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public $repass;
    public $loginname;
    public $rememberMe = true;

    public static function tableName ()
    {
        return "{{%user}}";
    }

    public function rules ()
    {
        return [
            ['loginname', 'required', 'message' => '登录用户名不能为空', 'on' => ['login']],
            ['openid', 'required', 'message' => 'openid不能为空', 'on' => ['qqreg']],
            ['username', 'required', 'message' => '用户名不能为空', 'on' => ['reg', 'regbymail', 'qqreg']],
            ['openid', 'unique', 'message' => 'openid已经被注册', 'on' => ['qqreg']],
            ['username', 'unique', 'message' => '用户已经被注册', 'on' => ['reg', 'regbymail', 'qqreg']],
            ['useremail', 'required', 'message' => '电子邮件不能为空', 'on' => ['reg', 'regbymail']],
            ['useremail', 'email', 'message' => '电子邮件格式不正确', 'on' => ['reg', 'regbymail']],
            ['useremail', 'unique', 'message' => '电子邮件已被注册', 'on' => ['reg', 'regbymail']],
            ['userpass', 'required', 'message' => '用户密码不能为空', 'on' => ['reg', 'login', 'regbymail', 'qqreg']],
            ['repass', 'required', 'message' => '确认密码不能为空', 'on' => ['reg', 'qqreg']],
            ['repass', 'compare', 'compareAttribute' => 'userpass', 'message' => '两次密码输入不一致', 'on' => ['reg', 'qqreg']],
            ['userpass', 'validatePass', 'on' => ['login']],
        ];
    }

    public function validatePass ()
    {
        if (!$this->hasErrors()) {
            $loginname = "username";
            if (preg_match('/@/', $this->loginname)) {
                $loginname = "useremail";
            }
            $data = self::find()->where($loginname . ' = :loginname and userpass = :pass', [
                ':loginname' => $this->loginname,
                ':pass'      => md5($this->userpass),
            ])->one();
            if (is_null($data)) {
                $this->addError("userpass", "用户名或者密码错误");
            }
        }
    }

    public function attributeLabels ()
    {
        return [
            'username'  => '用户名',
            'userpass'  => '用户密码',
            'repass'    => '确认密码',
            'useremail' => '电子邮箱',
            'loginname' => '用户名/电子邮箱',
        ];
    }

    /**
     * 用户注册
     * @param        $data
     * @param string $scenario
     * @return bool
     */
    public function reg ($data, $scenario = 'reg')
    {
        $this->scenario = $scenario;
        if ($this->load($data) && $this->validate()) {
            $this->createtime = time();
            $this->userpass   = md5($this->userpass);
            if ($this->save(false)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * 获取个人资料
     * @return \yii\db\ActiveQuery
     */
    public function getProfile ()
    {
        return $this->hasOne(Profile::className(), ['userid' => 'userid']);
    }

    /**
     * 获取用户实例
     */
    public function getUser ()
    {
        return self::find()->where('username =:loginname or useremail = :loginname', [':loginname' => $this->loginname])->one();
    }

    /**
     * 用户登录
     * @param $data
     * @return bool
     */
    public function login ($data)
    {
        $this->scenario = "login";
        if ($this->load($data) && $this->validate()) {
            //做点有意义的事
            $lifetime = $this->rememberMe ? 24 * 3600 : 0;
           return Yii::$app->user->login($this->getUser(), $lifetime);

            $session = Yii::$app->session;
            session_set_cookie_params($lifetime);
            $session['loginname'] = $this->loginname;
            $session['isLogin']   = 1;

            return (bool)$session['isLogin'];
        }

        return false;
    }

    /**
     * 通过邮箱注册
     * @param $data
     * @return bool
     */
    public function regByMail ($data)
    {
        $data['User']['username'] = 'i1_' . uniqid();
        $data['User']['userpass'] = uniqid();
        $this->scenario           = 'regbymail';
        if ($this->load($data) && $this->validate()) {
            $mailer = Yii::$app->mailer->compose('createuser', [
                'userpass' => $data['User']['userpass'],
                'username' => $data['User']['username'],
            ]);
            $mailer->setFrom('18380358053@163.com');
            $mailer->setTo($data['User']['useremail']);
            $mailer->setSubject('慕课商城-新建用户');

            if ($mailer->queue() && $this->reg($data, 'regbymail')) {
                return true;
            }
        }

        return false;
    }


    /**
     * 当前用户的身份实例，未认证则为null
     * @param int|string $id
     */
    public static function findIdentity ($id)
    {
        return static::findOne($id);

    }

    /**
     * 当前用户的id
     */
    public function getId ()
    {
        return $this->userid;
    }

    public function validateAuthKey ($authKey)
    {
        return true;
    }

    public static function findIdentityByAccessToken ($token, $type = NULL)
    {
        return NULL;
    }

    public function getAuthKey ()
    {
        return '';
    }


}
