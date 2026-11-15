<?php
/**
 * 用途：按 W02 契约建立独立的后台管理员账号表。
 * 作者：Clash727（W05）
 */

use yii\db\Migration;

class m261115_000001_create_account_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%account}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(64)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(64)->notNull(),
            'display_name' => $this->string(80)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->createIndex('uq_account_username', '{{%account}}', 'username', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%account}}');
    }
}
