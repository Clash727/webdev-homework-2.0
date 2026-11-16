<?php
/**
 * 用途：创建课设团队资源链接及其排序和外键约束。
 * 作者：zhixiu-feng
 */

use yii\db\Migration;

class m261116_300001_create_project_team_link_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%project_team_link}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_team_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(100)->notNull(),
            'url' => $this->string(2048)->notNull(),
            'sort_order' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('idx_project_team_link_team_sort', '{{%project_team_link}}', ['project_team_id', 'sort_order', 'id']);
        $this->addForeignKey('fk_project_team_link_team', '{{%project_team_link}}', 'project_team_id', '{{%project_team}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%project_team_link}}');
    }
}
