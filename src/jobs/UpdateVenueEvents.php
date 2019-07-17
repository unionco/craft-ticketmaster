<?php

namespace unionco\ticketmaster\jobs;

use craft\queue\BaseJob;
use union\ticketmaster\Plugin;

class UpdateVenueEvents extends BaseJob
{
    public $events;
    public $venue;
    public $siteHandle;

    public function execute($queue)
    {
        $eventService = Plugin::$plugin->events;
        $count = count($this->events);

        for ($step = 0; $step < $count; ++$step) {
            $this->setProgress($queue, $step / $count);

            $event = $this->events[$step];

            $eventDetails = $eventService->getEventDetails($event->id);

            if ($eventDetails) {
                // transform
                $readyEvent = $eventService->transform($eventDetails);
                $readyEvent['relatedPlace'] = [$this->venue['id']];

                // save
                $eventService->save($readyEvent, $this->siteHandle);
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
