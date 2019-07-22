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

namespace unionco\ticketmaster\events;

use craft\base\ElementInterface;
use yii\base\Event;

/**
 * Element action event class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class OnPublishEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var ElementInterface|null The element model associated with the event.
     */
    public $element;

    /**
     * @var ElementInterface|null The element model associated with the event.
     */
    public $tmEvent;

    /**
     * @var bool Whether the element is brand new
     */
    public $isNew = false;
}