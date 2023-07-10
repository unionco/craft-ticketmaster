<?php

namespace unionco\ticketmaster\jobs;

use craft\helpers\ArrayHelper;
use DateTime;
use craft\helpers\Json;
use craft\queue\BaseJob;
use Exception;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\services\LogService;
use unionco\ticketmaster\services\ElementService;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Venue as VenueRecord;
use unionco\ticketmaster\services\EventService;

class UpdateVenueEvents extends BaseJob
{
    public array $events = [];
    /** @var array This will be an array containing the key 'id' */
    public array $venue = [];
    public string $siteHandle = 'default';

    public function execute($queue): void
    {
        /** @var ElementService */
        $elementService = Ticketmaster::$plugin->elements;
        /** @var EventService */
        $eventService = Ticketmaster::$plugin->events;
        /** @var LogService */
        $log = Ticketmaster::$plugin->log;

        try {
            $venue = $this->getVenue();
            $logPrefix = "[Venue {$venue->title} {$venue->tmVenueId}] ";

            // Before looping, group the events based on their names
            $groupedEvents = $eventService->groupEventsByName($this->events);

            foreach ($groupedEvents as $name => $eventInstances) {
                // Each $eventInstance is the same 'Event', but with a unique performance date/time/URL
                $instances = [];
                foreach ($eventInstances as $i => $eventInstance) {
                    $loop1Index = $i + 1;
                    $key = "new{$loop1Index}";
                    $instances[$key] = $eventService->getEventSupertableInfo($eventInstance);
                }
                // Events have been grouped, so now proceed with the original plan
                // Event Instances supertable data will be added to the entry in
                // `ElementService::saveElement`
                $eventDetails = ArrayHelper::firstValue($eventInstances);
                $eventDetails['eventInstances'] = $instances;
                // save
                $description = $this->getEventDetailsDescription($eventDetails);
                $log->info("{$logPrefix}Event Details are present, starting save. $description");
                try {
                    $elementService->saveEvent($eventDetails, $venue);
                } catch (\Throwable $e) {
                    $log->error("{$logPrefix} Error encountered during ElementService::saveEvent.", $e);
                    throw new Exception("error in saveEvent - {$e->getMessage()}");
                }
            }
        } catch (\Throwable $e) {
            $log->error("Error encountered in UpdateVendueEvents job.", $e);
            throw $e;
        }
    }

    public function getVenue(): VenueModel
    {
        $venueRecord = VenueRecord::find()->where(['id' => $this->venue])->one();
        try {
            $venueModel = new VenueModel($venueRecord->getAttributes());
            return $venueModel;
        } catch (\Throwable $e) {
            /** @todo */
            throw new Exception("Error at getVenue");
        }
    }

    protected function defaultDescription(): ?string
    {
        if ($venue = $this->getVenue()) {
            return "Updating events for venue {$venue->title} [{$venue->tmVenueId}]";
        }
        return 'Updating events for venue (details not available)';
    }

    protected function getEventDetailsDescription(array $eventDetails): string
    {
        $desc = "";
        if ($name = $eventDetails['name'] ?? false) {
            $desc .= "[Name: $name] ";
        }

        if ($dateBlock = $eventDetails['dates'] ?? false) {
            if ($startBlock = $dateBlock['start'] ?? false) {
                $startDate = new DateTime($startBlock['dateTime']);
                $formatted = $startDate->format("Y-m-d H:i:s");
                $desc .= "[Date: $formatted] ";
            }
        }
        if (!$desc) {
            return "Description not available";
        }
        return $desc;
    }
}
