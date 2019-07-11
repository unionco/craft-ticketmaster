<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\db;

/**
 * This class provides constants for defining plugins database table names.
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
abstract class Table
{
    const VENUES = '{{%ticketmaster_venues}}';
    const EVENTS = '{{%ticketmaster_events}}';
    const STATUS = '{{%ticketmaster_event_status}}';
    const EVENT_ELEMENTS = '{{%ticketmaster_event_elements}}';
}
