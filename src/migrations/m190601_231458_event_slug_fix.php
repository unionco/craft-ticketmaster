<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190601_231458_event_slug_fix migration.
 */
class m190601_231458_event_slug_fix extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->addColumn(Table::EVENTS, 'slug', $this->integer()->after('payload'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190601_231458_event_slug_fix cannot be reverted.\n";
        return false;
    }
}
