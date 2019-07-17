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
use craft\base\ElementInterface;
use unionco\ticketmaster\db\Table;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\models\Event as EventModel;
use unionco\ticketmaster\records\Event as EventRecord;
use unionco\ticketmaster\fields\EventSearch;

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
     * {@inheritdoc}
     */
    public function afterElementSave(EventSearch $field, ElementInterface $element, bool $isNew)
    {
        $locale = $element->getSite()->language;
        $value = $element->getFieldValue($field->handle);

        $record = EventRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $field->id
            ]
        );

        if (!$record) {
            $record = new EventRecord();
            $record->ownerId = $element->id;
            $record->ownerSiteId = $element->siteId;
            $record->fieldId = $field->id;
        }

        $record->tmEventId = $value['tmEventId'];
        $record->title = $value['title'];

        $record->payload = json_encode($value['payload']);


        $record->save();

    }

    /**
     * {@inheritdoc}
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        if (!$value) {
            return;
        }
        /** @var ElementQuery $query */
        $tableName = Table::EVENTS;

        $query->join(
            'JOIN',
            "{$tableName} tmEvents",
            [
                'and',
                '[[elements.id]] = [[tmEvents.ownerId]]',
                '[[elements_sites.siteId]] = [[tmEvents.ownerSiteId]]',
            ]
        );

        return;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue(EventSearch $field, $value, ElementInterface $element = null)
    {
        $record = EventRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $field->id,
            ]
        );

        if (\Craft::$app->request->getIsPost() && $value) {
            $model = new EventModel($value);
        } elseif ($record) {
            $model = new EventModel($record->getAttributes());
        } else {
            $model = new EventModel();
        }

        return $model;
    }

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
