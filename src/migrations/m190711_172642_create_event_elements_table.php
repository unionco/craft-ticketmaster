<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190711_172642_create_event_elements_table migration.
 */
class m190711_172642_create_event_elements_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::EVENT_ELEMENTS, [
            'id' => $this->primaryKey(),
            'ownerId' => $this->integer()->notNull(),
            'ownerSiteId' => $this->integer()->notNull(),
            'fieldId' => $this->integer(),
            'tmVenueId' => $this->string()->notNull(),
            'tmEventId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'payload' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190711_172642_create_event_elements_table cannot be reverted.\n";
        return false;
    }
}
