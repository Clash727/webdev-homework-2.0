<?php
/**
 * 用途：课设团队资料、公开团队查询与成员关联。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $summary
 * @property ProjectMember[] $members
 */
class ProjectTeam extends ActiveRecord
{
    public const PUBLIC_SLUG = 'our-team';

    public static function tableName()
    {
        return '{{%project_team}}';
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
            [['slug', 'name', 'summary'], 'trim'],
            [['slug', 'name', 'summary'], 'required'],
            ['slug', 'string', 'max' => 64],
            ['slug', 'match', 'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D'],
            ['slug', 'unique'],
            ['name', 'string', 'max' => 100],
            ['summary', 'string', 'max' => 5000],
        ];
    }

    public static function findPublic(): ActiveQuery
    {
        return static::find()->where(['slug' => self::PUBLIC_SLUG]);
    }

    public function getMembers(): ActiveQuery
    {
        return $this->hasMany(ProjectMember::class, ['project_team_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
