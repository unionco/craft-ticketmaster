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

namespace unionco\ticketmaster\services;

use Craft;
use craft\helpers\Json;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\helpers\StringHelper;
use craft\base\ElementInterface;
use unionco\ticketmaster\db\Table;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\fields\EventSearch;
use unionco\ticketmaster\events\OnPublishEvent;
use unionco\ticketmaster\models\Event as EventModel;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Event as EventRecord;
use yii\db\ActiveRecordInterface;

/**
 * Base Service.
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class Events extends Base
{
    // Constants
    // =========================================================================

    /**
     * @var endpoint string
     */
    const ENDPOINT = 'discovery/v2/events';

    /**
     * @event OnPublishEvent The event that is triggered when publishing an event.
     *
     * ---
     * ```php
     * use unionco\ticketmaster\events\OnPublishEvent;
     * use unionco\ticketmaster\services\Events;
     * use yii\base\Event;
     *
     * Event::on(Events::class,
     *     Events::EVENT_BEFORE_PUBLISH,
     *     function(OnPublishEvent $event) {
     *         // do stuff
     *     }
     * );
     * ```
     */
    const EVENT_BEFORE_PUBLISH = 'beforePublish';

    /**
     * @event 
     */
    const EVENT_MISSING_FIELD_PUBLISH = 'missingFieldPublish';

    // Public Methods
    // =========================================================================

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
                'fieldId' => $field->id,
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

    public function getEventById(int $eventId)
    {
        return Craft::$app->getElements()->getElementById($eventId, Event::class);
    }

    /**
     * Get events from ticketmaster.
     *
     * @param venueId string (ticketmaster venue id)
     *
     * @return mixed
     */
    public function getEventsByVenueId(string $venueId)
    {
        $response = $this->makeRequest('GET', static::ENDPOINT, [
            'query' => [
                'venueId' => $venueId,
                'size' => 100,
                'source' => 'ticketmaster',
                'includeTBA' => 'no',
                'includeTBD' => 'no',
                'includeTest' => 'no',
            ],
        ]);

        if ($response['_embedded'] && $response['_embedded']['events']) {
            return $response['_embedded']['events'];
        }

        return [];
    }

    /**
     * Get details for single event from ticketmaster api
     * 
     * @param eventId string
     * 
     * @return mixed Json||boolean
     */
    public function getEventDetail(string $eventId)
    {
        $response = $this->makeRequest('GET', static::ENDPOINT.'/'.$eventId);

        if (isset($response['id'])) {
            return $response;
        }

        return false;
    }

    /**
     * Save ticketmaste event to event element.
     *
     * @param eventDetail array from tm
     * @param venue element
     *
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

        // md5 event payload vs md5 Json::encode(eventDetail)
        $event->isDirty = $this->isDirty($event, $eventDetail);

        $event->payload = Json::encode($eventDetail);

        try {
            $result = Craft::$app->getElements()->saveElement($event);
        } catch (\Throwable $th) {
            throw $th;
        }

        return $event;
    }

    /**
     * Determines if payload returned from TM is different from what is already in the database
     *
     * @param Event $event
     * @param array $eventDetails
     *
     * @return boolean
     */
    public function isDirty(Event $event, array $eventDetail)
    {
        if (! $event->payload) {
            return false;
        }

        return (md5($event->payload) !== md5(JSON::encode($eventDetail)));
    }

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
            $element = new Entry();
            $element->sectionId = $section->id;
            $element->typeId = $entryType;
            $element->title = $event->title;
            $element->slug = StringHelper::toKebabCase($event->title);
            $element->enabled = $enabled;

            $fieldLayoutFields = $element->getFieldLayout()->getFields();
            $eventSearchField = array_filter($fieldLayoutFields, function ($field) {
                return $field instanceof \unionco\ticketmaster\fields\EventSearch;
            });

            if ($eventSearchField) {
                $eventSearchField = array_pop($eventSearchField);
            } else {
                // throw error
                if ($this->hasEventHandlers(self::EVENT_MISSING_FIELD_PUBLISH)) {
                    $this->trigger(self::EVENT_MISSING_FIELD_PUBLISH, new OnPublishEvent([
                        'element' => $element,
                        'tmEvent' => $event,
                        'isNew' => true,
                    ]));
                }
                return true;
            }

            $element->{$eventSearchField->handle} = [
                'title' => $event->title,
                'tmEventId' => $event->tmEventId,
                'payload' => $event->published ? $event->_published() : $event->_payload(),
            ];

            // Fire a 'beforeSaveElement' event
            if ($this->hasEventHandlers(self::EVENT_BEFORE_PUBLISH)) {
                $this->trigger(self::EVENT_BEFORE_PUBLISH, new OnPublishEvent([
                    'element' => $element,
                    'tmEvent' => $event,
                    'isNew' => true
                ]));
            }

            return Craft::$app->getElements()->saveElement($element, $enabled ? true : false);
        }

        // find the elemenet that this record belongs to then fire off an event
        $element = $this->getTrueOwner($record);
        
        // Fire a 'beforeSaveElement' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_PUBLISH)) {
            $this->trigger(self::EVENT_BEFORE_PUBLISH, new OnPublishEvent([
                'element' => $element,
                'tmEvent' => $event,
                'isNew' => false
            ]));
        }

        $record->payload = $event->published ? $event->published : $event->payload;

        return $record->save();
    }

    /**
     * 
     */
    private function getTrueOwner(ActiveRecordInterface $record)
    {
        $owner = $record->getOwner()->one();
        
        // if its a matrix -> go up a level
        if ($owner->type === MatrixBlock::class) {
            $matrix = Craft::$app->getElements()->getElementById($owner->id, $owner->type);
            return Craft::$app->getElements()->getElementById($matrix->owner->id, \get_class($matrix->owner));
        }

        // TODO - Add support for supertable and neo

        return Craft::$app->getElements()->getElementById($owner->id, $owner->type);
    }
}
