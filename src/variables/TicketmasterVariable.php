<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\variables;

use Craft;

use unionco\ticketmaster\Ticketmaster;
use Adbar\Dot;

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

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.ticketmaster.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.ticketmaster.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function venues($criteria = [])
    {
        return Ticketmaster::$plugin->venues->getVenues();
    }

    public function getSetting($handle)
    {
        return Ticketmaster::$plugin->getSettings()->{$handle} ?? null;
    }

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

    public function makeDot($array = [])
    {
        $dot = new Dot($array);
        return $dot;
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
                [ 'name' => 'Images', 'handle' => 'images.*.url' ]
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
