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
            'tmVenueId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'type' => $this->string()->defaultValue('venue')->notNull(),
            'url' => $this->string()->notNull(),
            'payload' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(Table::EVENTS, [
            'id' => $this->primaryKey(),
            'venueId' => $this->integer()->notNull(),
            'tmEventId' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'type' => $this->string()->defaultValue('event')->notNull(),
            'url' => $this->string()->notNull(),
            'payload' => $this->text(),
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
        $this->addForeignKey(null, Table::VENUES, ['id'], '{{%elements}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::EVENTS, ['id'], '{{%elements}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::EVENTS, ['venueId'], Table::VENUES, ['id'], 'CASCADE', 'CASCADE');
    }
}
