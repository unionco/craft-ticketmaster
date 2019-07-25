<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use unionco\ticketmaster\elements\Event as EventElement;
use unionco\ticketmaster\elements\Venue as VenueElement;
use unionco\ticketmaster\fields\EventSearch;
use unionco\ticketmaster\fields\VenueSearch;
use unionco\ticketmaster\models\Settings;
use unionco\ticketmaster\services\Base as BaseService;
use unionco\ticketmaster\services\EventService;
use unionco\ticketmaster\services\ElementService;
use unionco\ticketmaster\services\VenueService;
use unionco\ticketmaster\twigextensions\TicketmasterTwigExtension;
use unionco\ticketmaster\variables\TicketmasterVariable;
use yii\base\Event;
use craft\events\DeleteElementEvent;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 *
 * @property  BaseService $base
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Ticketmaster extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Ticketmaster::$plugin
     *
     * @var Ticketmaster
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.7';

    /**
     * @var bool Whether the plugin has a settings page in the CP
     */
    public $hasCpSettings = true;

    /**
     * @var bool Whether the plugin has its own section in the CP
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Ticketmaster::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'elements' => ElementService::class,
            'events' => EventService::class,
            'venues' => VenueService::class,
        ]);

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'unionco\ticketmaster\console\controllers';
        }

        // Twig
        Craft::$app->view->registerTwigExtension(new TicketmasterTwigExtension());

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['GET ticketmaster'] = 'ticketmaster/cp/index';

                $event->rules['GET ticketmaster/venues'] = 'ticketmaster/venue/index';
                $event->rules['GET ticketmaster/venues/new'] = 'ticketmaster/venue/new';
                $event->rules['GET ticketmaster/venues/<id:\d+>'] = 'ticketmaster/venue/edit';

                $event->rules['GET ticketmaster/events'] = 'ticketmaster/event/index';
                $event->rules['GET ticketmaster/events/new'] = 'ticketmaster/event/edit-event';
                $event->rules['GET ticketmaster/events/<eventId:\d+><slug:(?:-[^\/]*)?>'] = 'ticketmaster/event/edit-event';

                $event->rules['GET ticketmaster/settings'] = 'ticketmaster/settings/index';
                $event->rules['GET ticketmaster/settings/sections'] = 'ticketmaster/settings/sections';
                $event->rules['GET ticketmaster/settings/layouts'] = 'ticketmaster/settings/layouts';
            }
        );


        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = VenueElement::class;
                $event->types[] = EventElement::class;
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = VenueSearch::class;
                $event->types[] = EventSearch::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('ticketmaster', TicketmasterVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'ticketmaster',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     *
     */
    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['url'] = 'ticketmaster';
        $item['subnav'] = [
            'ticketmaster' => [ 'label' => 'Dashboard', 'url' => 'ticketmaster' ],
            'events' => [ 'label' => 'Events', 'url' => 'ticketmaster/events' ],
            'settings' => [ 'label' => 'Settings', 'url' => 'settings/plugins/ticketmaster' ],
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'ticketmaster/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
