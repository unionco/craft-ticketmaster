<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place installation code here...
        if ($this->createTables()) {
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Place uninstallation code here...
        $this->dropTableIfExists(Table::EVENTS);
        $this->dropTableIfExists(Table::VENUES);
        return true;
    }

    /**
     *
     */
    protected function createTables()
    {
        $this->createTable(Table::VENUES, [
            'id' => $this->primaryKey(),
            'ownerId' => $this->integer()->notNull(),
            'ownerSiteId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'tmVenueId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'payload' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createIndex(
            null,
            Table::VENUES,
            ['ownerId', 'ownerSiteId', 'fieldId'],
            true
        );

        $this->createTable(Table::EVENTS, [
            'id' => $this->primaryKey(),
            'ownerId' => $this->integer()->notNull(),
            'ownerSiteId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'tmEventId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'payload' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createIndex(
            null,
            Table::EVENTS,
            ['ownerId', 'ownerSiteId', 'fieldId'],
            true
        );

        $this->createTable(Table::EVENT_ELEMENTS, [
            'id' => $this->primaryKey(),
            'tmVenueId' => $this->string()->notNull(),
            'tmEventId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'payload' => $this->text(),
            'published' => $this->text(),
            'isDirty' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        return true;
    }

    /**
     *
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, Table::VENUES, ['ownerId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::VENUES, ['ownerSiteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::VENUES, ['fieldId'], '{{%fields}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::EVENTS, ['ownerId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::EVENTS, ['ownerSiteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::EVENTS, ['fieldId'], '{{%fields}}', ['id'], 'CASCADE', 'CASCADE');
    }
}
