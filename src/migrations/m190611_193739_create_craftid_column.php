<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190611_193739_create_craftid_column migration.
 */
class m190611_193739_create_craftid_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->addColumn(Table::EVENTS, 'craftEntryId', $this->integer()->after('tmEventId'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190611_193739_create_craftid_column cannot be reverted.\n";
        return false;
    }
}
