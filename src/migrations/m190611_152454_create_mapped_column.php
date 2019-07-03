<?php

namespace unionco\ticketmaster\migrations;

use Craft;
use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190611_152454_create_mapped_column migration.
 */
class m190611_152454_create_mapped_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::EVENTS, 'mapped', $this->text()->after('payload'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190611_152454_create_mapped_column cannot be reverted.\n";
        return false;
    }
}
