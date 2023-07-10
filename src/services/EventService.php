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
use DateTime;
use craft\helpers\Json;
use yii\db\ActiveQuery;
use craft\helpers\ArrayHelper;
use craft\base\ElementInterface;
use verbb\supertable\SuperTable;
use unionco\ticketmaster\db\Table;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\fields\EventSearch;
use unionco\ticketmaster\models\Event as EventModel;
use unionco\ticketmaster\records\Event as EventRecord;

/**
 * Event Field Service.
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
class EventService extends Base
{
    /**
     * @var int The supertable block type ID for event instances.
     * Used to cache the result for performance.
     **/
    protected int $eventInstancesSuperTableBlockTypeId = 0;

    /**
     * Get event by ticketmaster event id
     *
     * @param string eventId
     *
     * @return EventRecord
     */
    public function getEventByEventId(string $eventId)
    {
        $record = $this->baseQuery();
        $record->andWhere(['tmEventId' => $eventId]);

        return $record->one();
    }

    /**
     * Get events from field data
     *
     * @return EventRecord[]
     */
    public function getEvents()
    {
        $records = $this->baseQuery();

        return $records->all();
    }

    /**
     * Base event query grouped by ownerid
     *
     * @return ActiveQuery
     */
    public function baseQuery()
    {
        $query = EventRecord::find();

        if (version_compare(Craft::$app->getInfo()->version, '3.2', '>=')) {
            $query->leftJoin('{{%elements}}', '[[ticketmaster_events.ownerId]] = [[elements.id]]');
            $query->where([
                'and',
                ['elements.revisionId' => null],
                ['elements.dateDeleted' => null],
            ]);
            $query->groupBy('ticketmaster_events.ownerId');
        }

        return $query;
    }

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
            $record = $this->createNewRecord($element, $field);
        }

        $record->tmEventId = $value['tmEventId'];
        $record->title = $value['title'];

        // unset the same fields as in ElementService so md5s match when checking for updates
        unset($value['tmEventId']);
        $value['tmEventId'] = $record->tmEventId;

        $record->payload = $this->handlePayload($value);

        $record->save();
    }

    /**
     * {@inheritdoc}
     */
    public function afterElementDelete(EventSearch $field, ElementInterface $element)
    {
        $value = $element->getFieldValue($field->handle);

        $record = EventRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $field->id,
            ]
        );

        if (!$record) {
            return true;
        }

        $element = Event::find()
            ->tmEventId($record->tmEventId)
            ->one();

        if ($element) {
            $element->isPublished = false;
            Craft::$app->getElements()->saveElement($element);
        }

        $record->delete();

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function modifyElementsQuery(EventSearch $field, ElementQueryInterface $query, $value)
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

        if (isset($query->{$field->handle})) {
            $query->where([
                'and',
                ['tmEventId' => $query->{$field->handle}],
            ]);
        }

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

        if (!Craft::$app->request->getIsConsoleRequest() && Craft::$app->request->getIsPost() && $value) {
            if ($value instanceof EventModel) {
                $value = $value->getAttributes();
            }
            $model = new EventModel($value);
        } elseif ($value instanceof EventModel) {
            $model = new EventModel($value->getAttributes());
        } elseif ($record) {
            $model = new EventModel($record->getAttributes());
        } else {
            $model = new EventModel();
        }

        return $model;
    }

    /**
     * Group the Ticketmaster events based on their name, so we can treat them
     * as the same event with multiple dates.
     *
     * @param array $events
     * @return array
     */
    public function groupEventsByName(array $events): array
    {
        $groupedEvents = [];
        // First, assign the names as array keys
        $uniqueEventNames = [];
        foreach ($events as $event) {
            $name = $event['name'] ?? false;
            if (!$name) {
                continue;
            }
            if (ArrayHelper::contains($uniqueEventNames, $name)) {
                continue;
            }
            $uniqueEventNames[] = $name;
        }

        // Next, group events by name
        foreach ($uniqueEventNames as $eventName) {
            $filterByEventNamePredicate = function (array $eventDetails) use ($eventName): bool {
                return ($eventDetails['name'] ?? false) == $eventName;
            };
            $matchingEvents = array_filter($events, $filterByEventNamePredicate);
            $groupedEvents[$eventName] = $matchingEvents;
        }

        return $groupedEvents;
    }

    /**
     * Transform the Ticketmaster API data (array) into the values we care about
     * for the supertable 'Event Instances' field.
     *
     * @param array $eventDetails
     * @return array
     */
    public function getEventSupertableInfo(array $eventDetails): array
    {
        $startDate = null;
        $url = '';
        try {
            $startDate = new DateTime($eventDetails['dates']['start']['dateTime']);
        } catch (\Throwable $e) {
            Ticketmaster::$plugin->log->error("No start date available, skipping");
            throw $e;
        }
        $url = $eventDetails['url'] ?? '';

        if (!$this->eventInstancesSuperTableBlockTypeId) {
            $field = Craft::$app->getFields()->getFieldByHandle('eventInstances');
            $blockTypes = SuperTable::$plugin->getService()->getBlockTypesByFieldId($field->id);
            $blockType = $blockTypes[0]; // There will only ever be one SuperTable_BlockType
            $this->eventInstancesSuperTableBlockTypeId = $blockType->id;
        }
        return [
            'type' => $this->eventInstancesSuperTableBlockTypeId,
            'enabled' => true,
            'fields' => [
                'eventDate' => $startDate,
                'ticketmasterLink' => $url,
            ]
        ];
    }

    /**
     * If no event record is found, create a new one
     *
     * @param ElementInterface $element
     * @param EventSearch $field
     *
     * @return EventRecord
     */
    private function createNewRecord(ElementInterface $element, EventSearch $field)
    {
        $record = new EventRecord();
        $record->ownerId = $element->id;
        $record->ownerSiteId = $element->siteId;
        $record->fieldId = $field->id;

        return $record;
    }

    /**
     * Ensures the payload is in the proper JSON format
     *
     * @param mixed $value
     *
     * @return string
     */
    private function handlePayload($value)
    {
        if (!is_string($value['payload'])) {
            return Json::encode($value['payload']) ?? '';
        }

        return $value['payload'];
    }
}
