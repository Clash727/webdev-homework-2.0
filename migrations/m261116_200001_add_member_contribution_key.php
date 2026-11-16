<?php
/**
 * 用途：为课设成员增加稳定的分工关联标识，改名及排序不改变贡献归属。
 * 作者：zhixiu-feng
 */

use yii\db\Migration;

class m261116_200001_add_member_contribution_key extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%project_member}}', 'contribution_key', $this->string(16)->null());
        $this->createIndex('uq_member_team_contribution', '{{%project_member}}', ['project_team_id', 'contribution_key'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('uq_member_team_contribution', '{{%project_member}}');
        $this->dropColumn('{{%project_member}}', 'contribution_key');
    }
}
