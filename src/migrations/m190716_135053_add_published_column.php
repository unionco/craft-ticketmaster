<?php

namespace unionco\ticketmaster\migrations;

use craft\db\Migration;
use unionco\ticketmaster\db\Table;

/**
 * m190716_135053_add_published_column migration.
 */
class m190716_135053_add_published_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->addColumn(Table::EVENT_ELEMENTS, 'published', $this->text()->after('payload'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190716_135053_add_published_column cannot be reverted.\n";
        return false;
    }
}
