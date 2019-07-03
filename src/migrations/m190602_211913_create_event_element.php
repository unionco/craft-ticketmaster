<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;
use craft\helpers\MigrationHelper;

/**
 * m190602_211913_create_event_element migration.
 */
class m190602_211913_create_event_element extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        MigrationHelper::dropForeignKeyIfExists(Table::EVENTS, ['status'], $this);

        // Place migration code here...
        if ($this->db->columnExists(Table::EVENTS, 'title')) {
            $this->dropColumn(Table::EVENTS, 'title');
        }

        if ($this->db->columnExists(Table::EVENTS, 'slug')) {
            $this->dropColumn(Table::EVENTS, 'slug');
        }

        if ($this->db->columnExists(Table::EVENTS, 'status')) {
            $this->dropColumn(Table::EVENTS, 'status');
        }

        $this->dropTableIfExists(Table::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190602_211913_create_event_element cannot be reverted.\n";
        return false;
    }
}
