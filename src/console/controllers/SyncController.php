<?php
namespace unionco\ticketmaster\console\controllers;

use Craft;
use yii\console\Controller;
use unionco\ticketmaster\jobs\UpdateEvents;

class SyncController extends Controller
{
    /**
     * Fetch events for all venues
     *
     * @return string
     */
    public function actionIndex()
    {
        $queue = Craft::$app->getQueue();
        $queue->push(new UpdateEvents([
            "siteHandle" => "default"
        ]));
        $queue->run();
    }
}
