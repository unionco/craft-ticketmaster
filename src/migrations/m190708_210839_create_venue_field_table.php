<?php

namespace unionco\ticketmaster\migrations;

use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190708_210839_create_venue_field_table migration.
 */
class m190708_210839_create_venue_field_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Table::VENUES, 'ownerId', $this->integer()->after('id'));
        $this->addColumn(Table::VENUES, 'ownerSiteId', $this->integer()->after('ownerId'));
        $this->addColumn(Table::VENUES, 'fieldId', $this->integer()->after('ownerSiteId'));
        // Place migration code here...
        $this->createIndex(
            null,
            Table::VENUES,
            ['ownerId', 'ownerSiteId', 'fieldId'],
            true
        );

        $this->addForeignKey(
            null,
            Table::VENUES,
            ['ownerId'],
            '{{%elements}}',
            ['id'],
            'CASCADE',
            null
        );

        $this->addForeignKey(
            null,
            Table::VENUES,
            ['ownerSiteId'],
            '{{%sites}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            null,
            Table::VENUES,
            ['fieldId'],
            '{{%fields}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190708_210839_create_venue_field_table cannot be reverted.\n";

        return false;
    }
}
