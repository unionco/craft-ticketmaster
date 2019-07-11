<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;
use craft\helpers\MigrationHelper;

/**
 * m190711_182300_remove_event_element_columns migration.
 */
class m190711_182300_remove_event_element_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->columnExists(Table::EVENT_ELEMENTS, 'ownerId')) {
            $this->dropColumn(Table::EVENT_ELEMENTS, 'ownerId');
        }
        if ($this->db->columnExists(Table::EVENT_ELEMENTS, 'ownerSiteId')) {
            $this->dropColumn(Table::EVENT_ELEMENTS, 'ownerSiteId');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190711_182300_remove_event_element_columns cannot be reverted.\n";
        return false;
    }
}
