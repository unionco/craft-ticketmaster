<?php

namespace unionco\ticketmaster\jobs;

use craft\queue\BaseJob;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Venue as VenueRecord;

class UpdateVenueEvents extends BaseJob
{
    public $events;
    public $venue;
    public $siteHandle;

    public function execute($queue)
    {
        $eventService = Ticketmaster::$plugin->elements;
        $count = count($this->events);
        $venueRecord = VenueRecord::find()->where(['id' => $this->venue])->one();
        $venueModel = new VenueModel($venueRecord->getAttributes());

        for ($step = 0; $step < $count; ++$step) {
            $this->setProgress($queue, $step / $count);

            $eventDetails = $this->events[$step];

            if ($eventDetails) {
                // save
                $eventService->saveEvent($eventDetails, $venueModel);
            } else {
                return false;
            }
        }

        return true;
    }

    protected function defaultDescription()
    {
        return 'Updating events for venue.';
    }
}
