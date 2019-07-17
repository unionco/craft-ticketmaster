<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190717_192307_add_dirty_column_to_event_elements_table migration.
 */
class m190717_192307_add_dirty_column_to_event_elements_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::EVENT_ELEMENTS, 'isDirty', $this->boolean()->after('published'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190717_192307_add_dirty_column_to_event_elements_table cannot be reverted.\n";
        return false;
    }
}
