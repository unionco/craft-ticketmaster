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
use craft\base\ElementInterface;
use unionco\ticketmaster\db\Table;
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
 *
 * @since     1.0.0
 */
class EventService extends Base
{
    public function getEventByEventId(string $eventId)
    {
        $record = $this->baseQuery();
        $record->andWhere(['tmEventId' => $eventId]);

        return $record->one();
    }

    public function getEvents()
    {
        $records = $this->baseQuery();

        return $records->all();
    }

    public function baseQuery()
    {
        $query = EventRecord::find();

        if (version_compare(Craft::$app->getInfo()->version, '3.2', '>=')) {
            $query->leftJoin('{{%elements}}', '[[ticketmaster_events.ownerId]] = [[elements.id]]');
            $query->where([
                'and',
                [
                    'not',
                    ['elements.revisionId' => null],
                ],
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

        $record->payload = $this->handlePayload($value);

        $record->save();
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
        if (! is_string($value['payload'])) {
            return Json::encode($value['payload']) ?? '';
        }

        return $value['payload'];
    }
}
