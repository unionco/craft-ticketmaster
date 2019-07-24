<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\assetbundles\Ticketmaster;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;
use craft\redactor\assets\redactor\RedactorAsset;

/**
 * TicketmasterAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class TicketmasterAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@unionco/ticketmaster/assetbundles/ticketmaster/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            RedactorAsset::class,
            VueAsset::class,
        ];

        if (getenv('TM_ENABLE_DEV') === 'true') {
			$this->js = [
				'http://localhost:8080/app.js',
			];
		} else {
			$this->css = [
				'css/app.css',
			];

			$this->js = [
				'js/app.js',
				'js/chunk-vendors.js',
			];
		}

        parent::init();
    }
}
