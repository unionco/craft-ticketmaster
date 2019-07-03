<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
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
 * @package   Ticketmaster
 * @since     1.0.0
 */
class Event extends ActiveRecord
{

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return Table::EVENTS;
    }

    /**
     * Returns the section’s structure.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getVenue(): ActiveQueryInterface
    {
        return $this->hasOne(Table::VENUES, ['id' => 'venueId']);
    }

    /**
     * Returns the section’s structure.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getStatus(): ActiveQueryInterface
    {
        return $this->hasOne(Table::STATUS, ['id' => 'status']);
    }
}
