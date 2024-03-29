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

/**
 * Settings Controller
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
    protected array|int|bool $allowAnonymous = false;

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
