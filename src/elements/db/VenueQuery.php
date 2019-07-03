<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\elements\db;

// use Craft;
use craft\helpers\Db;
use craft\elements\db\ElementQuery;

/**
 * EventQuery represents a SELECT SQL statement for events in a way that is independent of DBMS.
 *
 */
class VenueQuery extends ElementQuery
{
    // Public Properties
    // =========================================================================
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $tmVenueId;

    // Public Methods
    // =========================================================================

    /**
     * 
     */
    public function title($value)
    {
        $this->title = $value;

        return $this;
    }

    /**
     * 
     */
    public function venueId($value)
    {
        $this->tmVenueId = $value;

        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('ticketmaster_venues');

        // select the columns
        $this->query->select([
            'ticketmaster_venues.tmVenueId',
            'ticketmaster_venues.title',
            'ticketmaster_venues.type',
            'ticketmaster_venues.url',
            'ticketmaster_venues.payload'
        ]);

        if ($this->title) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_venues.title', $this->title));
        }

        if ($this->tmVenueId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_venues.tmVenueId', $this->tmVenueId));
        }

        return parent::beforePrepare();
    }
}