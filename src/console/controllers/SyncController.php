<?php
namespace union\ticketmaster\console;

use Yii;
use Craft;
use Exception;
use yii\console\Controller;
use yii\helpers\Console;
use union\ticketmaster\Plugin;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use union\ticketmaster\jobs\UpdateEvents;

class SyncController extends Controller
{
    /**
     * Fetch events for all venues
     *
     * @return string
     */
    public function actionIndex()
    {
        Yii::$app->log->targets = [];

        $queue = Craft::$app->getQueue();
        $queue->push(new UpdateEvents([
            "siteHandle" => "default"
        ]));
        $queue->run();
    }
}
