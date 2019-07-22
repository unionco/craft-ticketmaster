<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\console\controllers;

use Craft;

use yii\helpers\Console;
use yii\console\Controller;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\records\Event as EventRecord;

/**
 * Base Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft ticketmaster/base
 *
 * Actions must be in 'kebab-case' so actionDoSomething() maps to 'do-something',
 * and would be invoked via:
 *
 * ./craft ticketmaster/base/do-something
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle ticketmaster/base console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $record = EventRecord::findOne([
            'tmEventId' => 'G5eVZ4ISja_f7'
        ]);
        
        // var_dump($record);
        var_dump(get_class($record->getOwner()->one()));

        exit();
    }

    /**
     * Handle ticketmaster/base/do-something console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console BaseController actionDoSomething() method\n";

        return $result;
    }
}
