<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190722_143314_add_isDirty_column_to_ticketmaster_events_table migration.
 */
class m190722_143314_add_isDirty_column_to_ticketmaster_events_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::EVENTS, 'isDirty', $this->boolean()->after('payload'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190722_143314_add_isDirty_column_to_ticketmaster_events_table cannot be reverted.\n";
        return false;
    }
}
