<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190601_163731_fix_events_table migration.
 */
class m190601_163731_fix_events_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->dropColumn(Table::EVENTS, 'title');
        $this->dropColumn(Table::EVENTS, 'type');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190601_163731_fix_events_table cannot be reverted.\n";
        return false;
    }
}
