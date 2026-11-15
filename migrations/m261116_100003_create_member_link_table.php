<?php
/**
 * 用途：创建成员公开链接表及成员关联。
 * 作者：zhixiu-feng
 */

use yii\db\Migration;

class m261116_100003_create_member_link_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%member_link}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_member_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(100)->notNull(),
            'url' => $this->string(2048)->notNull(),
            'sort_order' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('idx_member_link_member_sort', '{{%member_link}}', ['project_member_id', 'sort_order', 'id']);
        $this->addForeignKey('fk_member_link_member', '{{%member_link}}', 'project_member_id', '{{%project_member}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%member_link}}');
    }
}
