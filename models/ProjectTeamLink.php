<?php
/**
 * 用途：团队资源链接的校验、时间记录和所属团队关联。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\UrlValidator;

class ProjectTeamLink extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%project_team_link}}';
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
            [['project_team_id', 'title', 'url'], 'required'],
            ['project_team_id', 'integer', 'min' => 1, 'max' => 4294967295],
            ['sort_order', 'integer', 'min' => 0, 'max' => 4294967295],
            ['title', 'string', 'max' => 100],
            ['url', 'string', 'max' => 2048],
            ['url', 'url', 'validSchemes' => ['http', 'https']],
            ['project_team_id', 'exist', 'targetClass' => ProjectTeam::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getTeam(): ActiveQuery
    {
        return $this->hasOne(ProjectTeam::class, ['id' => 'project_team_id']);
    }

    public function isPublicUrl(): bool
    {
        return (new UrlValidator(['validSchemes' => ['http', 'https']]))->validate($this->url);
    }
}
