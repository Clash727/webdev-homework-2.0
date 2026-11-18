<?php

use yii\db\Migration;

class m261118_000002_add_fixture_bracket_fields extends Migration
{
    public function safeUp()
    {
        $table = '{{%fixture}}';
        $this->addColumn($table, 'home_source', $this->string(87));
        $this->addColumn($table, 'away_source', $this->string(87));
        $this->addColumn($table, 'winner_side', $this->string(4));
        $this->addColumn($table, 'decided_by', $this->string(16));
        $this->addColumn($table, 'home_penalties', $this->smallInteger()->unsigned());
        $this->addColumn($table, 'away_penalties', $this->smallInteger()->unsigned());
    }

    public function safeDown()
    {
        foreach (['home_source', 'away_source', 'winner_side', 'decided_by', 'home_penalties', 'away_penalties'] as $column) {
            $this->dropColumn('{{%fixture}}', $column);
        }
    }
}
