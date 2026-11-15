<?php
/**
 * 用途：持久化后台管理员并提供共享的 Yii 会话身份接口。
 * 作者：Clash727（W05）
 */

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class Account extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%account}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => static fn () => gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'display_name'], 'trim'],
            ['username', 'filter', 'filter' => 'strtolower', 'skipOnArray' => true],
            [['username', 'display_name', 'password_hash', 'auth_key'], 'required'],
            ['username', 'match', 'pattern' => '/\A[a-z0-9_]{3,64}\z/',
                'message' => '用户名须为 3–64 位小写字母、数字或下划线。'],
            ['username', 'unique', 'message' => '用户名已存在，请使用其他用户名。'],
            ['display_name', 'string', 'max' => 80],
            ['password_hash', 'string', 'max' => 255],
            ['auth_key', 'string', 'min' => 32, 'max' => 64],
        ];
    }

    public function attributeLabels(): array
    {
        return ['username' => '用户名', 'display_name' => '展示名'];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findByUsername($username): ?self
    {
        return static::findOne(['username' => strtolower(trim($username))]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return Yii::$app->security->compareString($this->getAuthKey(), $authKey);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}
