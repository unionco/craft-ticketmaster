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
use unionco\ticketmaster\elements\Event;

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
    public $tmVenueId;

    /**
     * @var string
     */
    public $tmEventId;

    /**
     * @var string
     */
    public $status;


    // Public Methods
    // =========================================================================

    /**
     *
     */
    public function tmVenueId($value)
    {
        $this->tmVenueId = $value;

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
    public function status($value)
    {
        return parent::status($value);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('ticketmaster_event_elements');

        // select the columns
        $this->query->select([
            'ticketmaster_event_elements.title',
            'ticketmaster_event_elements.tmVenueId',
            'ticketmaster_event_elements.tmEventId',
            'ticketmaster_event_elements.payload',
            'ticketmaster_event_elements.published',
            'ticketmaster_event_elements.isDirty',
        ]);

        if ($this->tmVenueId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_event_elements.tmVenueId', $this->tmVenueId));
        }

        if ($this->tmEventId) {
            $this->subQuery->andWhere(Db::parseParam('ticketmaster_event_elements.tmEventId', $this->tmEventId));
        }

        return parent::beforePrepare();
    }

    public function statusCondition(string $status)
    {
        switch($this->status) {
            case Event::STATUS_PUBLISHED:
                return [
                    'and',
                    [
                        // 'ticketmaster_event_elements.isPublished' => true,
                    ]
                ];
            case Event::STATUS_UPDATED:
                return [
                    'and',
                    [
                        // 'ticketmaster_event_elements.isPublished' => true,
                        'ticketmaster_event_elements.isDirty' => true,
                    ]
                ];
            case Event::STATUS_NEW:
                return [
                    'and',
                    [
                        // 'ticketmaster_event_elements.isPublished' => false,
                        'ticketmaster_event_elements.isDirty' => false,
                    ]
                ];
            default:
                return [];
        }
    }
}
