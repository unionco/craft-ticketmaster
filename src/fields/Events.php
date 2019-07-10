<?php

namespace unionco\ticketmaster\fields;

use craft\fields\BaseRelationField;
use unionco\ticketmaster\elements\Event;

class Events extends BaseRelationField
{
    public static function displayName(): string
    {
        return \Craft::t('ticketmaster', 'Events');
    }

    protected static function elementType(): string
    {
        return Event::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('ticketmaster', 'Select an event');
    }
}
