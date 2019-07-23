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

use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use unionco\ticketmaster\db\Table;
use unionco\ticketmaster\fields\VenueSearch;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Venue as VenueRecord;

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
class VenueService extends Base
{
    // Public Methods
    // =========================================================================
    const ENDPOINT = 'discovery/v2/venues';

    public function getVenueById(string $venueId)
    {
        $record = $this->baseQuery();
        $record->andWhere(['tmVenueId' => $venueId]);

        return $record->one();
    }

    public function getVenues()
    {
        $records = $this->baseQuery();

        return $records->all();
    }

    public function baseQuery()
    {
        $query = VenueRecord::find();
        $query->leftJoin('{{%elements}}', '[[ticketmaster_venues.ownerId]] = [[elements.id]]');
        $query->where([
            'and',
            [
                'not', 
                ['elements.revisionId' => null]
            ],
            ['elements.dateDeleted' => null]
        ]);
        $query->groupBy('ticketmaster_venues.ownerId');

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function afterElementSave(VenueSearch $field, ElementInterface $element, bool $isNew)
    {

        /** @var Element $owner */
        $locale = $element->getSite()->language;
        /** @var Map $value */
        $value = $element->getFieldValue($field->handle);

        $record = VenueRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $field->id,
            ]
        );

        if (!$record) {
            $record = new VenueRecord();
            $record->ownerId = $element->id;
            $record->ownerSiteId = $element->siteId;
            $record->fieldId = $field->id;
        }

        $record->tmVenueId = $value['tmVenueId'] ?? '';
        $record->title = $value['title'] ?? '';

        if (!is_string($value['payload'])) {
            $record->payload = Json::encode($value['payload']) ?? '';
        } else {
            $record->payload = $value['payload'];
        }

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
        $tableName = Table::VENUES;

        $query->join(
            'JOIN',
            "{$tableName} tmVenues",
            [
                'and',
                '[[elements.id]] = [[tmVenues.ownerId]]',
                '[[elements_sites.siteId]] = [[tmVenues.ownerSiteId]]',
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
    public function normalizeValue(VenueSearch $field, $value, ElementInterface $element = null)
    {
        $record = VenueRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $field->id,
            ]
        );

        if (\Craft::$app->request->getIsPost() && $value) {
            $model = new VenueModel($value);
        } elseif ($record) {
            $model = new VenueModel($record->getAttributes());
        } else {
            $model = new VenueModel();
        }

        return $model;
    }
}
