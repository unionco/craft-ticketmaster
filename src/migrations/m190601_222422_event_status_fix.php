<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;
use craft\helpers\StringHelper;

/**
 * m190601_222422_event_status_fix migration.
 */
class m190601_222422_event_status_fix extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->createTable(Table::STATUS, [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'uid' => $this->uid()
        ]);

        // seed it
        $status = [
            ['Pending', StringHelper::UUID()],
            ['Published', StringHelper::UUID()],
            ['Updated', StringHelper::UUID()]
        ];

        $rawTableName = $this->db->getSchema()->getRawTableName(Table::STATUS);

        $this->batchInsert($rawTableName, [
            'title',
            'uid'
        ], $status, false);

        $this->addColumn(Table::EVENTS, 'status', $this->integer()->after('payload')->defaultValue(1));

        $this->addForeignKey(null, Table::EVENTS, ['status'], Table::STATUS, ['id']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190601_222422_event_status_fix cannot be reverted.\n";
        return false;
    }
}
