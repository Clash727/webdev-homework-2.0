<?php
/**
 * 用途：创建课设团队表。
 * 作者：zhixiu-feng
 */

use yii\db\Migration;

class m261116_100001_create_project_team_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%project_team}}', [
            'id' => $this->primaryKey()->unsigned(),
            'slug' => $this->string(64)->notNull()->defaultValue('our-team'),
            'name' => $this->string(100)->notNull(),
            'summary' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('uq_project_team_slug', '{{%project_team}}', 'slug', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%project_team}}');
    }
}
