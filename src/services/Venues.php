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
use unionco\ticketmaster\elements\Venue;

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
class Venues extends Base
{
    // Public Methods
    // =========================================================================
    const ENDPOINT = 'discovery/v2/venues';

    public function getVenueById(int $venueId)
    {
        return Craft::$app->getElements()->getElementById($venueId, Venue::class);
    }
}
