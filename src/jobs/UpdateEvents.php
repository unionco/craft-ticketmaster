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

    public function execute($queue)
    {
        $venues = Ticketmaster::$plugin->venues->getVenues();

        $count = count($venues);

        for ($step = 0; $step < $count; ++$step) {
            $this->setProgress($queue, $step / $count);

            $events = Ticketmaster::$plugin->events->getEventByVenueId($venues[$step]->tmVenueId);

            Craft::$app->queue->push(new UpdateVenueEvents([
                    'description' => 'Fetching ('.count($events).") events for {$venues[$step]->title} in {$this->siteHandle}",
                    'events' => $events,
                    'venue' => [
                        'id' => $venues[$step]->id,
                    ],
                    'siteHandle' => $this->siteHandle,
                ]));
        }

        return true;
    }

    protected function defaultDescription()
    {
        return 'Fetching all events.';
    }
}
