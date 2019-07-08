<?php

namespace unionco\ticketmaster\fields;

use craft\fields\BaseRelationField;
use unionco\ticketmaster\elements\Venue;

class Venues extends BaseRelationField
{
    public static function displayName(): string
    {
        return \Craft::t('ticketmaster', 'Venues');
    }

    protected static function elementType(): string
    {
        return Venue::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('ticketmaster', 'Select a venue');
    }
}
