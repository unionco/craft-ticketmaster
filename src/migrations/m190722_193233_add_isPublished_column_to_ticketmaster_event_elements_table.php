<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190722_193233_add_isPublished_column_to_ticketmaster_event_elements_table migration.
 */
class m190722_193233_add_isPublished_column_to_ticketmaster_event_elements_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::EVENT_ELEMENTS, 'isPublished', $this->boolean()->after('isDirty'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190722_193233_add_isPublished_column_to_ticketmaster_event_elements_table cannot be reverted.\n";
        return false;
    }
}
