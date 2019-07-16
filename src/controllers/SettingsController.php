<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\controllers;

use Craft;

use craft\web\Controller;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;

/**
 * Base Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class SettingsController extends BaseController
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = false;

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/ticketmaster/base
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $variables = [];

        return $this->renderTemplate('ticketmaster/cp/settings', $variables);
    }

    /**
     * 
     */
    public function actionGetEntryTypes()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $uid = $request->getBodyParam('uid');

        $section = Craft::$app->getSections()->getSectionByUid($uid);

        $types = array_map(function ($type) {
            return [
                "label" => $type->name,
                "value" => $type->id
            ];
        }, $section->getEntryTypes());

        return $this->asJson([
            'success' => true,
            'types' => $types
        ]);
    }

    /**
     * 
     */
    public function actionGetFieldLayout()
    {
        $this->requirePostRequest();

        $settings = Ticketmaster::$plugin->getSettings();

        $request = Craft::$app->getRequest();

        $id = $request->getBodyParam('entryType');

        $entryType = Craft::$app->sections->getEntryTypeById($id);

        $fields = array_map(function ($field) use($settings) {
            return [
                "id" => $field->id,
                "name" => $field->name,
                "handle" => $field->handle,
                "map" => $settings->getSectionMapField($field->handle) ?? null,
                "fieldType" => get_class($field)
            ];
        }, $entryType->getFields());

        array_unshift($fields, [
            "id" => 0,
            "name" => "Title",
            "handle" => "title",
            "map" => $settings->getSectionMapField("title") ?? null,
            "fieldType" => "craft\\fields\\PlainText"
        ]);

        return $this->asJson([
            'success' => true,
            'fields' => $fields
        ]);
    }
}
