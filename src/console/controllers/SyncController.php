<?php
namespace unionco\ticketmaster\console\controllers;

use Yii;
use Craft;
use Exception;
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
        // What does this do?
        // Yii::$app->log->targets = [];

        $queue = Craft::$app->getQueue();
        $queue->push(new UpdateEvents([
            "siteHandle" => "default"
        ]));
        $queue->run();
    }
}
