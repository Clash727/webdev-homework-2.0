<?php
/**
 * 用途：创建课设成员表及团队关联。
 * 作者：zhixiu-feng
 */

use yii\db\Migration;

class m261116_100002_create_project_member_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%project_member}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_team_id' => $this->integer()->unsigned()->notNull(),
            'display_name' => $this->string(80)->notNull(),
            'role' => $this->string(100)->notNull(),
            'bio' => $this->text()->notNull(),
            'sort_order' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('idx_project_member_team_sort', '{{%project_member}}', ['project_team_id', 'sort_order', 'id']);
        $this->addForeignKey('fk_project_member_team', '{{%project_member}}', 'project_team_id', '{{%project_team}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%project_member}}');
    }
}
