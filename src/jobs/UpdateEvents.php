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

namespace unionco\ticketmaster\jobs;

use unionco\ticketmaster\Ticketmaster;
use Craft;
use craft\queue\BaseJob;
use unionco\ticketmaster\jobs\UpdateVenueEvents;

/**
 * UpdateEvents job.
 *
 * Jobs are run in separate process via a Queue of pending jobs. This allows
 * you to spin lengthy processing off into a separate PHP process that does not
 * block the main process.
 *
 * You can use it like this:
 *
 * use unionco\ticketmaster\jobs\UpdateEvents as UpdateEventsJob;
 *
 * $queue = Craft::$app->getQueue();
 * $jobId = $queue->push(new UpdateEventsJob([
 *     'description' => Craft::t('ticketmaster', 'This overrides the default description'),
 *     'someAttribute' => 'someValue',
 * ]));
 *
 * The key/value pairs that you pass in to the job will set the public properties
 * for that object. Thus whatever you set 'someAttribute' to will cause the
 * public property $someAttribute to be set in the job.
 *
 * Passing in 'description' is optional, and only if you want to override the default
 * description.
 *
 * More info: https://github.com/yiisoft/yii2-queue
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class UpdateEvents extends BaseJob
{
    public $siteHandle;

    public function execute($queue): void
    {
        $venues = Ticketmaster::$plugin->venues->getVenues();
        $log = Ticketmaster::$plugin->log;
        $count = count($venues);
        $log->info("Starting sync via UpdateEvents queue job (based on $count venues)");

        $queue = Craft::$app->getQueue();

        try {
            // for ($step = 0; $step < $count; ++$step) {
            foreach ($venues as $step => $venue) {
                $this->setProgress($queue, $step / $count);
                $venueId = $venue->tmVenueId;
                $title = $venue->title;
                $events = Ticketmaster::$plugin->elements->getEventsByVenueId($venueId);
                $count = count($events);
                $log->info("[$step] Processing venue ($title) with ID: $venueId");
                if (!$count) {
                    $log->info("No events for current venue. Skipping.");
                    continue;
                }
                $queue->push(new UpdateVenueEvents([
                    'description' => "Fetching ($count) events for {$title} in {$this->siteHandle}",
                    'events' => $events,
                    'venue' => [
                        'id' => $venue->id,
                    ],
                    'siteHandle' => $this->siteHandle,
                ]));
                $log->info("Added queue job. Sleeping for 2 seconds.");
                sleep(2);
            }
        } catch (\Throwable $e) {
            $log->error("Error encountered in UpdateEvents job.", $e);
            throw $e;
        }
    }

    protected function defaultDescription(): ?string
    {
        return 'Fetching all events.';
    }
}
