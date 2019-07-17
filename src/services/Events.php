<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\services;

use Craft;
use Adbar\Dot;
use craft\helpers\Json;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Event as EventRecord;
// use unionco\ticketmaster\models\Event as EventModel;

/**
 * Base Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class Events extends Base
{
    // Public Methods
    // =========================================================================
    const ENDPOINT = "discovery/v2/events";

    /**
     *
     */
    public function getEventById(int $eventId)
    {
        return Craft::$app->getElements()->getElementById($eventId, Event::class);
    }

    /**
     * Get events from ticketmaster
     *
     * @param venueId string (ticketmaster venue id)
     * @return mixed
     */
    public function getEventByVenueId(string $venueId)
    {
        $response = $this->makeRequest('GET', static::ENDPOINT, [
            "query" => [
                'venueId' => $venueId,
                'size' => 100,
                'source' => 'ticketmaster',
                'includeTBA' => 'no',
                'includeTBD' => 'no',
                'includeTest' => 'no',
            ]
        ]);

        if ($response['_embedded'] && $response['_embedded']['events']) {
            return $response['_embedded']['events'];
        }

        return [];
    }

    /**
     *
     */
    public function getEventDetail(string $eventId)
    {
        $response = $this->makeRequest('GET', static::ENDPOINT . "/" . $eventId);

        if (isset($response['id'])) {
            return $response;
        }

        return false;
    }

    /**
     * Save ticketmaste event to event element
     *
     * @param eventDetail array from tm
     * @param venue element
     * @return mixed Throwable||Event;
     */
    public function saveEvent(array $eventDetail, VenueModel $venue)
    {
        $event = Event::find()
            ->tmEventId($eventDetail['id'])
            ->one();

        if (!$event) {
            $event = new Event();
            $event->tmVenueId = $venue->tmVenueId;
            $event->title = $eventDetail['name'];
        }

        $event->tmEventId = $eventDetail['id'];
        $eventDetail['tmEventId'] = $event->tmEventId;

        // unset certain fields we dont need in the payload
        unset($eventDetail['name']);
        unset($eventDetail['id']);

        $event->payload = Json::encode($eventDetail);

        try {
            $result = Craft::$app->getElements()->saveElement($event);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $event;
    }

    /**
     *
     */
    public function publishEvent(Event $event)
    {
        $settings = Ticketmaster::$plugin->getSettings();
        $enabled = $settings->enableWhenPublish;
        $section = Craft::$app->getSections()->getSectionByUid($settings->section);
        $entryType = $settings->sectionEntryType;

        $record = EventRecord::findOne(
            [
                'tmEventId' => $event->tmEventId,
            ]
        );

        if (!$record) { 
            // create a new entry && get field layout
            $craftEvent = new Entry();
            $craftEvent->sectionId = $section->id;
            $craftEvent->typeId = $entryType;
            $craftEvent->title = $event->title;
            $craftEvent->slug = StringHelper::toKebabCase($event->title);
            $craftEvent->enabled = $enabled;

            $fieldLayoutFields = $craftEvent->getFieldLayout()->getFields();
            $eventSearchField = array_filter($fieldLayoutFields, function ($field) {
                return $field instanceof \unionco\ticketmaster\fields\EventSearch;
            });
            
            if ($eventSearchField) {
                $eventSearchField = array_pop($eventSearchField);
            } else {
                // throw error
            }

            $craftEvent->{$eventSearchField->handle} = [
                "title" => $event->title,
                "tmEventId" => $event->tmEventId,
                "payload" => $event->published ? $event->_published() : $event->_payload()
            ];

            return Craft::$app->getElements()->saveElement($craftEvent, $enabled ? true: false);
        }

        $record->payload = $event->published ? $event->published : $event->payload;
        return $record->save();
    }
}