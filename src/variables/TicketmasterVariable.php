<?php
/**
 * Ticketmaster plugin for Craft CMS 4.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\variables;

use Craft;
use craft\base\FieldInterface;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use unionco\ticketmaster\models\Venue as VenueModel;

/**
 * Ticketmaster Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.ticketmaster }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class TicketmasterVariable
{
    // Public Methods
    // =========================================================================

    public function events($criteria = [])
    {
        $query = Event::find();
        Craft::configure($query, $criteria);
        
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function venues($criteria = [])
    {
        $venueRecords = Ticketmaster::$plugin->venues->getVenues();
        $venues = [];

        foreach ($venueRecords as $key => $record) {
            $venues[] = new VenueModel($record);
        }

        return $venues;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetting($handle)
    {
        return Ticketmaster::$plugin->getSettings()->{$handle} ?? null;
    }

    /**
     * Created a field dynamically
     * 
     * @param class stirng
     * @param params array
     * 
     * @return FieldInterface
     */
    public function createField($class, $params)
    {
        $field = new $class();
        Craft::configure($field, $params);

        return $field;
    }

    public function getSectionSelect()
    {
        $sections = Craft::$app->getSections()->getSectionsByType('channel');

        $options = [];

        foreach ($sections as $key => $section) {
            $options[] = [
                "label" => $section->name,
                "value" => $section->uid
            ];
        }

        return $options;
    }

    public function getApiFields()
    {
        return [
            [ 'group' => 'Basic', 'options' => [
                [ 'name' => 'Id', 'handle' => 'id' ],
                [ 'name' => 'Name', 'handle' => 'name' ],
                [ 'name' => 'URL', 'handle' => 'url' ],
                [ 'name' => 'Info', 'handle' => 'info' ],
                [ 'name' => 'Please Note', 'handle' => 'pleaseNote' ],
                [ 'name' => 'Seatmap', 'handle' => 'seatmap.staticUrl' ],
                [ 'name' => 'Ticket Limit', 'handle' => 'ticketLimit.info' ],
            ]],
            [ 'group' => 'Images', 'options' => [
                [ 'name' => 'Image Url', 'handle' => 'images.*.url' ],
                [ 'name' => 'Image Ratio', 'handle' => 'images.*.ratio' ],
                [ 'name' => 'Image Width', 'handle' => 'images.*.width' ],
                [ 'name' => 'Image Height', 'handle' => 'images.*.height' ]
            ]],
            [ 'group' => 'Dates', 'options' => [
                [ 'name' => 'Start -> Datetime', 'handle' => 'dates.start.dateTime' ],
                [ 'name' => 'End -> Datetime', 'handle' => 'dates.end.dateTime' ],
                [ 'name' => 'Timezone', 'handle' => 'dates.timezone' ],
                [ 'name' => 'Spans Multiple Days', 'handle' => 'dates.spanMultipleDays' ],
                [ 'name' => 'Status', 'handle' => 'dates.status.code' ],
            ]],
            [ 'group' => 'Classifications', 'options' => [
                [ 'name' => 'Segment', 'handle' => 'classifications.0.segment.name' ],
                [ 'name' => 'Genre', 'handle' => 'classifications.0.genre.name' ],
                [ 'name' => 'Type', 'handle' => 'classifications.0.type.name' ],
                [ 'name' => 'Family', 'handle' => 'classifications.0.family' ],
            ]],
            [ 'group' => 'Price Ranges', 'options' => [
                [ 'name' => 'Min', 'handle' => 'priceRanges.*.min' ],
                [ 'name' => 'Max', 'handle' => 'priceRanges.*.max' ],
                [ 'name' => 'Currency', 'handle' => 'priceRanges.*.currency' ],
            ]],
            [ 'group' => 'Sales', 'options' => [
                [ 'name' => 'Start Datetime', 'handle' => 'sales.public.startDateTime' ],
                [ 'name' => 'End Datetime', 'handle' => 'sales.public.endDateTime' ],
            ]]
        ];
    }
}
