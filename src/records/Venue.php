<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x.
 *
 * Ticket master ticket feed for venues.
 *
 * @see      https://github.com/unionco
 *
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use unionco\ticketmaster\db\Table;

/**
 * Class Event record.
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class Venue extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return Table::VENUES;
    }
}
