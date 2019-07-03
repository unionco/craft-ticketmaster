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
class EventQuery extends ElementQuery
{
    // Public Properties
    // =========================================================================
    

    /**
     * @var int
     */
    public $venueId;

    /**
     * @var string
     */
    public $tmEventId;

    /**
     * @var int
     */
    public $craftEntryId;


    // Public Methods
    // =========================================================================

    /**
     * 
     */
    public function venueId($value)
    {
        $this->venueId = $value;

        return $this;
    }

    /**
     * 
     */
    public function tmEventId($value)
    {
        $this->tmEventId = $value;

        return $this;
    }

    /**
     * 
     */
    public function craftEntryId($value)
    {
        $this->craftEntryId = $value;

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
        $this->joinElementTable('ticketmaster_events');

        // select the columns
        $this->query->select([
            'ticketmaster_events.venueId',
            'ticketmaster_events.tmEventId',
            'ticketmaster_events.craftEntryId',
            'ticketmaster_events.url',
            'ticketmaster_events.payload',
            'ticketmaster_events.published'
        ]);

        if ($this->venueId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_events.venueId', $this->venueId));
        }

        if ($this->tmEventId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_events.tmEventId', $this->tmEventId));
        }
        
        if ($this->craftEntryId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_events.craftEntryId', $this->craftEntryId));
        }

        return parent::beforePrepare();
    }
}