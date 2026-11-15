<?php
/**
 * 用途：课设成员资料校验与所属团队、个人链接关联。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $project_team_id
 * @property string $display_name
 * @property string $role
 * @property string $bio
 * @property int $sort_order
 * @property ProjectTeam $team
 * @property MemberLink[] $links
 */
class ProjectMember extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%project_member}}';
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
            [['display_name', 'role', 'bio'], 'trim'],
            ['bio', 'default', 'value' => ''],
            ['sort_order', 'default', 'value' => 0],
            [['project_team_id', 'display_name', 'role'], 'required'],
            ['project_team_id', 'integer', 'min' => 1, 'max' => 4294967295],
            ['sort_order', 'integer', 'min' => 0, 'max' => 4294967295],
            ['display_name', 'string', 'max' => 80],
            ['role', 'string', 'max' => 100],
            ['bio', 'string', 'max' => 5000],
            ['project_team_id', 'exist', 'targetClass' => ProjectTeam::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getTeam(): ActiveQuery
    {
        return $this->hasOne(ProjectTeam::class, ['id' => 'project_team_id']);
    }

    public function getLinks(): ActiveQuery
    {
        return $this->hasMany(MemberLink::class, ['project_member_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
