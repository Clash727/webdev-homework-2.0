<?php
/** 用途：创建 W08 留言表，供 W12 审核复用。作者：Clash727 */
use yii\db\Migration;

class m261117_000001_create_guest_message_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%guest_message}}', [
            'id' => $this->primaryKey()->unsigned(),
            'nickname' => $this->string(80)->notNull(),
            'body' => $this->text()->notNull(),
            'status' => $this->string(16)->notNull()->defaultValue('pending'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('idx_guest_message_status_created', '{{%guest_message}}',
            ['status', 'created_at', 'id']);
        $this->execute("ALTER TABLE {{%guest_message}} ADD CONSTRAINT chk_guest_message_status "
            . "CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function safeDown()
    {
        $this->dropTable('{{%guest_message}}');
    }
}
