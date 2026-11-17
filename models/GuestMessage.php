<?php
/** 用途：验证纯文本留言，新留言统一待审，提供共享公开查询。作者：Clash727（W08） */
namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class GuestMessage extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%guest_message}}';
    }

    public function behaviors(): array
    {
        return [['class' => TimestampBehavior::class,
            'value' => static fn () => gmdate('Y-m-d H:i:s')]];
    }

    public function rules(): array
    {
        return [
            [['nickname', 'body'], 'trim'],
            [['nickname', 'body'], 'required'],
            ['nickname', 'string', 'max' => 80],
            ['body', 'string', 'max' => 2000],
            ['status', 'in', 'range' => [
                self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED,
            ]],
        ];
    }

    public function scenarios(): array
    {
        return [self::SCENARIO_DEFAULT => ['nickname', 'body', '!status']];
    }

    public function attributeLabels(): array
    {
        return ['nickname' => '昵称', 'body' => '留言'];
    }

    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->status = self::STATUS_PENDING;
        }
        return parent::beforeSave($insert);
    }

    public static function findApproved(): ActiveQuery
    {
        return static::find()->where(['status' => self::STATUS_APPROVED]);
    }
}
