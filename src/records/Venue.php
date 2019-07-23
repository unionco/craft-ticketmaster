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
use craft\records\Element;
use craft\records\Field;
use craft\records\Site;
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

    /**
     * Returns the tickmaster event's owner.
     *
     * @return ActiveQueryInterface the relational query object
     */
    public function getOwner(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'ownerId']);
    }

    /**
     * Returns the tickmaster event's owner's site.
     *
     * @return ActiveQueryInterface the relational query object
     */
    public function getOwnerSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'ownerSiteId']);
    }

    /**
     * Returns the tickmaster event's field.
     *
     * @return ActiveQueryInterface the relational query object
     */
    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }
}
