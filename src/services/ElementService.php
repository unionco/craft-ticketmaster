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
use Exception;
use craft\helpers\Json;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\MatrixBlock;
use craft\helpers\StringHelper;
use craft\base\ElementInterface;
use yii\db\ActiveRecordInterface;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use unionco\ticketmaster\services\LogService;
use unionco\ticketmaster\events\OnPublishEvent;
use unionco\ticketmaster\models\Venue as VenueModel;

/**
 * Element Service.
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class ElementService extends Base
{
    // Constants
    // =========================================================================

    /**
     * @var string endpoint
     */
    const ENDPOINT = 'discovery/v2/events.json';

    /**
     * @event OnPublishEvent The event that is triggered when publishing an event.
     *
     * ---
     * ```php
     * use unionco\ticketmaster\events\OnPublishEvent;
     * use unionco\ticketmaster\services\ElementService;
     * use yii\base\Event;
     *
     * Event::on(ElementService::class,
     *     ElementService::EVENT_BEFORE_PUBLISH,
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
     * Get Event for given ID.
     *
     * @param int $eventId
     *
     * @return ElementInterface|null
     */
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
     * Get details for single event from ticketmaster api.
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
     * Save ticketmaster event to event element.
     *
     * @param eventDetail array from tm
     * @param venue element
     *
     * @return mixed Throwable||Event;
     */
    public function saveEvent(array $eventDetail, VenueModel $venue)
    {
        $entry =  Entry::find()
            ->site('boplex')
            ->status(null)
            ->section('boplexEvents')
            ->where(['content.field_ticketmasterId' => $eventDetail['id']])
            ->one();
        /** @var LogService */
        $log = Ticketmaster::$plugin->log;

        $detail = $this->transform($eventDetail);
        $prefix = $detail['name'];
        /** IMPORTANT - 2023-07-10, this method previously never updated
         * an entry if it already existed. Changing this behavior to update event
         * entries always UNLESS it is locked.
         */
        if (!$entry) {
            $log->info("[$prefix] Event entry does not exist. Creating it");
            $entry = new Entry();
            $entry->enabled = false;
            $entry->siteId = 7;
            $entry->sectionId = 54;
            $entry->typeId = 122;
            // Continue below and set values + save
        } elseif ($entry->getFieldValue('lock')) {
            $log->info("[$prefix] This event is locked. Skipping.");
            return $entry;
        }

        $entry->setFieldValue('ticketmasterId', $detail['id']);
        $entry->setFieldValue('tm_eventImage', $detail['tm_eventImage']);
        $entry->setFieldValue('tm_startDate', $detail['tm_startDate']);
        $entry->setFieldValue('tm_endDate', $detail['tm_endDate']);

        if (isset($detail['startTime'])) {
            $date = \DateTime::createFromFormat(
                'H:i:s',
                $detail['startTime']
            );
            $entry->setFieldValue('startTime', $date);
        }

        $entry->setFieldValue('priceMax', $detail['priceMax']);
        $entry->setFieldValue('priceMin', $detail['priceMin']);
        $entry->setFieldValue('tm_buttonLink', $detail['url']);
        // 2023-07-10 - Adding this for the Event Instances field ST field
        $entry->setFieldValue('eventInstances', $eventDetail['eventInstances']);

        if ($venue['title'] == 'Ovens Auditorium') {
            $entry->setFieldValue('boplexVenue', [74142]);
        } else {
            $entry->setFieldValue('boplexVenue', [74141]);
        }

        $entry->title = $detail['name'];

        if ($category = $this->category($detail['relatedEventCategory'])) {
            $entry->setFieldValue('boplexEventCategory', [$category->id]);
            $entry->boplexEventCategory = [$category->id];
        } else {
            $category = new Category();
            $category->groupId = 21;
            $category->title = $detail['relatedEventCategory'];
            $category->slug = StringHelper::toKebabCase(
                $detail['relatedEventCategory']
            );
            $category->siteId = 7;

            $result = Craft::$app->getElements()->saveElement($category);

            if (!$result) {
                return false;
            }

            $entry->setFieldValue('boplexEventCategory', [$category->id]);


            try {
                $result = Craft::$app->getElements()->saveElement($entry);

                if (!$result) {
                    throw new Exception('Did not save entry');
                    return false;
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        return $entry;
    }

    /**
     * Determines if payload returned from TM is different from what is already in the database.
     *
     * @param Event $event
     * @param array $eventDetail
     *
     * @return bool
     */
    public function isDirty(Event $event, string $hash)
    {
        // if its already dirty, keep it dirty
        if ($event->isDirty) {
            return true;
        }

        if (!$event->eventHash) {
            return false;
        }

        return $event->eventHash !== $hash;
    }

    /**
     * Publish the event.
     *
     * @param Event $event
     *
     * @return bool
     */
    public function publishEvent(Event $event)
    {
        $settings = Ticketmaster::$plugin->getSettings();
        $enabled = $settings->enableWhenPublish;
        $section = Craft::$app->getSections()->getSectionByUid($settings->section);
        $siteIds = $section->getSiteIds();
        $entryType = $settings->sectionEntryType;

        $record = Ticketmaster::$plugin->events->getEventByEventId($event->tmEventId);

        if (!$record) {
            // create a new entry && get field layout
            $element = new Entry();
            $element->sectionId = $section->id;
            $element->typeId = $entryType;
            $element->title = $event->title;
            $element->slug = StringHelper::toKebabCase($event->title);
            $element->enabled = $enabled;
            $element->siteId = array_shift($siteIds);

            $fieldLayoutFields = $element->getFieldLayout()->getCustomFields();
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
                    'isNew' => true,
                ]));
            }

            Craft::$app->getElements()->saveElement($element, $enabled ? true : false);

            return $element;
        }

        // find the elemenet that this record belongs to then fire off an event
        $element = $this->getTrueOwner($record);

        // Fire a 'beforeSaveElement' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_PUBLISH)) {
            $this->trigger(self::EVENT_BEFORE_PUBLISH, new OnPublishEvent([
                'element' => $element,
                'tmEvent' => $event,
                'isNew' => false,
            ]));
        }

        $record->payload = $event->published ? $event->published : $event->payload;
        $record->save();

        return $element;
    }

    /**
     * Attempts to find the true owner of the record, even if it's an instance of a Matrix Block.
     *
     * @param ActiveRecordInterface $record
     *
     * @return ElementInterface
     */
    private function getTrueOwner(ActiveRecordInterface $record)
    {
        $owner = $record->getOwner()->one();

        // todo:: fix this
        if ($owner->type === MatrixBlock::class) {
            $matrix = Craft::$app->getElements()->getElementById($owner->id, $owner->type);

            return Craft::$app->getElements()->getElementById($matrix->owner->id, \get_class($matrix->owner));
        }

        // TODO - Add support for supertable and neo
        $element = $owner->type::find();
        $element->id = $record->ownerId;
        $element->siteId = $record->ownerSiteId;
        $element->status = null;

        return $element->one();
    }

    public function transform(array $eventDetails): array
    {
        $event['name'] = $eventDetails['name'];
        $event['id'] = $eventDetails['id'];
        $event['url'] = $eventDetails['url'];

        // times
        $event['startTime'] = $eventDetails['dates']['start']['localTime'] ?? null;
        $event['tm_startDate'] = $eventDetails['dates']['start']['localDate'] ?? 'TBD';
        $event['tm_endDate'] = $eventDetails['dates']['end']['localDate'] ?? $eventDetails['dates']['start']['localDate'] ?? null;
            
        // pricing
        if (isset($eventDetails['priceRanges']) && count($eventDetails['priceRanges'])) {
            $event['priceMax'] = $eventDetails['priceRanges'][count($eventDetails['priceRanges']) - 1]['max'];
            $event['priceMin'] = $eventDetails['priceRanges'][0]['min'];
        } else {
            $event['priceMax'] = '';
            $event['priceMin'] = '';
        }

        // description
        $description = [];
        $description[] = $eventDetails['info'] ?? '';
        // $description[] = isset($eventDetails->accessibility) ? $eventDetails->accessibility->info : '';
        $event['eventDescription'] = implode("\n", $description);

        // image
        $event['tm_eventImage'] = array_values(array_filter($eventDetails['images'], function($image) {
            return $image['width'] >= 1000 && ($image['ratio'] ?? '') === '16_9' && str_contains($image['url'], 'RETINA_LANDSCAPE');
        }))[0]['url'];

        // category
        $event['relatedEventCategory'] = array_values(array_filter($eventDetails['classifications'], function($classification) {
            return $classification['primary'] == true;
        }))[0]['segment']['name'];

        if ($event['relatedEventCategory'] === 'Music') {
            $event['relatedEventCategory'] = 'concerts';
        }

        // 2023-07-10 adding event instances ST data
        $event['eventInstances'] = $eventDetails['eventInstances'];

        return $event;
    }

    public function category($slug)
    {
        return Category::find()
            ->site('boplex')
            ->groupId(21)
            ->slug('*' . StringHelper::toKebabCase($slug) . "*")
            ->one();
    }
}
