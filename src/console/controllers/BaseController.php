<?php
/**
 * Ticketmaster plugin for Craft CMS 4.x
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
}
