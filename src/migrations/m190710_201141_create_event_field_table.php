<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190710_201141_create_event_field_table migration.
 */
class m190710_201141_create_event_field_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::EVENTS, 'ownerId', $this->integer()->after('id'));
        $this->addColumn(Table::EVENTS, 'ownerSiteId', $this->integer()->after('ownerId'));
        $this->addColumn(Table::EVENTS, 'fieldId', $this->integer()->after('ownerSiteId'));
        $this->createIndex(
            null,
            Table::EVENTS,
            ['ownerId', 'ownerSiteId', 'fieldId'],
            true
        );

        $this->addForeignKey(
            null,
            Table::EVENTS,
            ['ownerId'],
            '{{%elements}}',
            ['id'],
            'CASCADE',
            null
        );

        $this->addForeignKey(
            null,
            Table::EVENTS,
            ['ownerSiteId'],
            '{{%sites}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            null,
            Table::EVENTS,
            ['fieldId'],
            '{{%fields}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190710_201141_create_event_field_table cannot be reverted.\n";
        return false;
    }
}
