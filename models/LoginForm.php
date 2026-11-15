<?php
/**
 * 用途：校验管理员登录凭证并建立会话，统一错误凭证提示。
 * 作者：Clash727（W05）
 */

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules(): array
    {
        return [
            ['username', 'trim'],
            ['username', 'filter', 'filter' => 'strtolower', 'skipOnArray' => true],
            [['username', 'password'], 'required'],
            ['username', 'match', 'pattern' => '/\A[a-z0-9_]{3,64}\z/',
                'message' => '用户名或密码错误。'],
            ['password', 'string', 'max' => 72, 'encoding' => '8bit'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['username' => '用户名', 'password' => '密码'];
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $account = Account::findByUsername($this->username);
        if ($account === null || !$account->validatePassword($this->password)) {
            $this->addError('password', '用户名或密码错误。');
            return false;
        }

        return Yii::$app->user->login($account);
    }
}
