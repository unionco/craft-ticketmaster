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
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use unionco\ticketmaster\models\Venue as VenueModel;

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
        $event->payload = Json::encode($eventDetail);

        try {
            $result = Craft::$app->getElements()->saveElement($event);
            // $event->validate();
            // die(var_dump($event->getErrors()));
        } catch (\Throwable $th) {
            throw $th;
        }

        return $event;
    }

    /**
     *
     */
    public function transformPayload(array $data)
    {
        $dot = new Dot($data);

        return [
            "id" => $dot->get('id'),
            "description" => [
                "label" => "Description",
                "field" => "craft\\fields\\PlainText",
                "value" => $dot->get('description'),
                "config" => ["handle" => "payload[description]", "multiline" => true, "initialRows" => 4]
            ],
            "url" => [
                "label" => "Url",
                "field" => "craft\\fields\\Url",
                "value" => $dot->get('url'),
                "config" => ["handle" => "payload[url]"]
            ],
            "startDate" => [
                "label" => "Start Date",
                "field" => "craft\\fields\\Date",
                "value" => $dot->get('dates.start.dateTime'),
                "config" => ["handle" => "payload[startDate]", "showDate" => true, "showTime" => true]
            ],
            "endDate" => [
                "label" => "End Date",
                "field" => "craft\\fields\\Date",
                "value" => $dot->get('dates.end.dateTime'),
                "config" => ["handle" => "payload[endDate]", "showDate" => true, "showTime" => true]
            ],
            "spanMultipleDays" => [
                "label" => "Spans Multiple Days",
                "field" => "craft\\fields\\Lightswitch",
                "value" => (bool) $dot->get('dates.spanMultipleDays'),
                "config" => ["handle" => "payload[spanMultipleDays]"],
            ],
            "status" => [
                "label" => "Event Status",
                "field" => "craft\\fields\\PlainText",
                "value" => $dot->get('dates.status.code'),
                "config" => ["handle" => "payload[status]", "multiline" => false]
            ],
            "info" => [
                "label" => "Info",
                "field" => "craft\\fields\\PlainText",
                "value" => $dot->get('info'),
                "config" => ["handle" => "payload[info]", "multiline" => true, "initialRows" => 4]
            ],
            "additionalInfo" => [
                "label" => "Additional Info",
                "field" => "craft\\fields\\PlainText",
                "value" => $dot->get('additionalInfo'),
                "config" => ["handle" => "payload[additionalInfo]", "multiline" => true, "initialRows" => 4]
            ],
            "pleaseNote" => [
                "label" => "Please Note",
                "field" => "craft\\fields\\PlainText",
                "value" => $dot->get('pleaseNote'),
                "config" => ["handle" => "payload[pleaseNote]", "multiline" => true, "initialRows" => 4]
            ],
            "seatmap" => [
                "label" => "Seat Map",
                "field" => "craft\\fields\\Url",
                "value" => $dot->get('seatmap.staticUrl'),
                "config" => ["handle" => "payload[seatmap]"],
                "thumb" => true
            ],
            "images" => [
                "label" => "Images",
                "field" => "craft\\fields\\Table",
                "value" => array_map(function($image) {
                    return ["col1" => $image['url']];
                }, $dot->get('images')),
                "config" => [
                    "handle" => "payload[images]",
                    "columns" => [
                        "col1" => [ "heading" => "Image", "handle" => "image", "type" => "singleline" ]
                    ]
                ],
            ]
        ];
    }

    /**
     *
     */
    public function publishEvent(Event $event)
    {

    }
}
