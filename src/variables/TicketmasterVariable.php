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
use unionco\ticketmaster\elements\Venue;
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
        $query = Venue::find();
        Craft::configure($query, $criteria);

        return $query;
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
            [ 'name' => 'Id', 'handle' => 'id' ],
            [ 'name' => 'Name', 'handle' => 'name' ],
            [ 'name' => 'URL', 'handle' => 'url' ],
            [ 'name' => 'Images', 'handle' => 'images' ],
            [ 'name' => 'Seatmap', 'handle' => 'seatmap' ],
            [ 'name' => 'Classifications', 'handle' => 'classifications' ],
            [ 'name' => 'Dates', 'handle' => 'dates' ],
            [ 'name' => 'Price Ranges', 'handle' => 'priceRanges' ],
            [ 'name' => 'Sales', 'handle' => 'sales' ],
            [ 'name' => 'TicketLimit', 'handle' => 'ticketLimit' ],
        ];
    }
}