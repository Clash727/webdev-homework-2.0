<?php
/**
 * 用途：成员公开链接校验与排序，限制为 HTTP(S) 地址。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\UrlValidator;

/**
 * @property int $id
 * @property int $project_member_id
 * @property string $title
 * @property string $url
 * @property int $sort_order
 */
class MemberLink extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%member_link}}';
    }

    public function behaviors()
    {
        return [['class' => TimestampBehavior::class, 'value' => static function () {
            return gmdate('Y-m-d H:i:s');
        }]];
    }

    public function rules()
    {
        return [
            [['title', 'url'], 'trim'],
            ['sort_order', 'default', 'value' => 0],
            [['project_member_id', 'title', 'url'], 'required'],
            ['project_member_id', 'integer', 'min' => 1, 'max' => 4294967295],
            ['sort_order', 'integer', 'min' => 0, 'max' => 4294967295],
            ['title', 'string', 'max' => 100],
            ['url', 'string', 'max' => 2048],
            ['url', 'url', 'validSchemes' => ['http', 'https']],
            ['project_member_id', 'exist', 'targetClass' => ProjectMember::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getMember(): ActiveQuery
    {
        return $this->hasOne(ProjectMember::class, ['id' => 'project_member_id']);
    }

    /** 即使历史数据绕过模型写入，也不输出不安全协议。 */
    public function isPublicUrl(): bool
    {
        return (new UrlValidator(['validSchemes' => ['http', 'https']]))->validate($this->url);
    }
}
