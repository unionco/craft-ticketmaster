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

namespace unionco\ticketmaster\fields;

use Craft;
use yii\db\Schema;
use craft\base\Field;
use craft\base\ElementInterface;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\assetbundles\Ticketmaster\TicketmasterAsset;
use unionco\ticketmaster\db\Table;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\models\Event as EventModel;
use unionco\ticketmaster\records\Event as EventRecord;

/**
 * VenueFinder Field.
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class EventSearch extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool propagate
     */
    public $key = false;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string the display name of this class
     */
    public static function displayName(): string
    {
        return Craft::t('ticketmaster', 'Event Search');
    }

    /**
     * {@inheritdoc}
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['key'], 'required'];

        return $rules;
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     *                to convert the give column type to the physical one. For example, `string` will be converted
     *                as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     *                appended as well.
     *
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $record = EventRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $this->id,
            ]
        );

        if (\Craft::$app->request->getIsPost() && $value) {
            $model = new EventModel($value);
        } elseif ($record) {
            $model = new EventModel($record->getAttributes());
        } else {
            $model = new EventModel();
        }

        return $model;
    }

    /**
     * Modifies an element query.
     *
     * This method will be called whenever elements are being searched for that may have this field assigned to them.
     *
     * If the method returns `false`, the query will be stopped before it ever gets a chance to execute.
     *
     * @param ElementQueryInterface $query The element query
     * @param mixed                 $value the value that was set on this field’s corresponding [[ElementCriteriaModel]] param,
     *                                     if any
     *
     * @return false|null `false` in the event that the method is sure that no elements are going to be found
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * Returns the component’s settings HTML.
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="foo">'.$this->getSettings()->foo.'</textarea>';
     * ```
     *
     * For more complex settings, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template loacated at
     * craft/plugins/myplugin/templates/_settings.html, passing the settings to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_settings', [
     *     'settings' => $this->getSettings()
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your settings, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getSettingsHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] service provides a couple handy methods that can help you deal
     * with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getSettingsHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getSettingsHtml()
     * {
     *     // Come up with an ID value for 'foo'
     *     $id = Craft::$app->getView()->formatInputId('foo');
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'settings'     => $this->getSettings()
     *     ]);
     * }
     * ```
     *
     * And the _settings.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="foo">{{ settings.foo }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'ticketmaster/_includes/fields/venue-search-settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="'.$name.'">'.$value.'</textarea>';
     * ```
     *
     * For more complex inputs, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template located at
     * craft/plugins/myplugin/templates/_fieldinput.html, passing the $name and $value variables to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *     'name'  => $name,
     *     'value' => $value
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your input, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getInputHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] provides a couple handy methods that can help you deal with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getInputHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getInputHtml($value, $element)
     * {
     *     // Come up with an ID value based on $name
     *     $id = Craft::$app->getView()->formatInputId($name);
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'name'         => $name,
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'value'        => $value
     *     ]);
     * }
     * ```
     *
     * And the _fieldinput.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="{{ name }}">{{ value }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
     * @param mixed                 $value   The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                       raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return string the input HTML
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        $view = Craft::$app->getView();

        // Get our id and namespace
        $containerId = $this->id.'-container';
        $vueContainerId = $view->namespaceInputId($containerId);
        $settings = Ticketmaster::$plugin->getSettings();
        $apiKey = $settings->getConsumerKey();

        $view->registerAssetBundle(TicketmasterAsset::class);
        $view->registerJs('new Vue({ el: \'#'.$vueContainerId.'\' });');
        $options = preg_replace(
            '/\'/',
            '&#039;',
            json_encode([
                'apiKey' => $apiKey,
                'handle' => $this->handle,
            ])
        );
        $venue = preg_replace(
            '/\'/',
            '&#039;',
            $value->toJson()
        );

        return '<div id="'.$containerId.'"><venue-search :venue=\''. $venue.'\' :options=\''.$options.'\'></venue-search></div>';
    }

    /**
     * {@inheritdoc}
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        $locale = $element->getSite()->language;
        $value = $element->getFieldValue($this->handle);

        $record = EventRecord::findOne(
            [
                'ownerId' => $element->id,
                'ownerSiteId' => $element->siteId,
                'fieldId' => $this->id,
            ]
        );

        if (!$record) {
            $record = new EventRecord();
            $record->ownerId = $element->id;
            $record->ownerSiteId = $element->siteId;
            $record->fieldId = $this->id;
        }

        $record->tmVenueId = $value['tmVenueId'];
        $record->title = $value['title'];
        $record->url = '';

        $record->payload = json_encode($value['payload']);

        $record->save();

        parent::afterElementSave($element, $isNew);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        if (!$value) {
            return;
        }
        /** @var ElementQuery $query */
        $tableName = Table::EVENTS;

        $query->join(
            'JOIN',
            "{$tableName} tmEvents",
            [
                'and',
                '[[elements.id]] = [[tmEvents.ownerId]]',
                '[[elements_sites.siteId]] = [[tmEvents.ownerSiteId]]',
            ]
        );

        return;
    }
}
