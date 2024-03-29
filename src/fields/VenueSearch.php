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
use unionco\ticketmaster\assetbundles\ticketmaster\TicketmasterAsset;
use craft\elements\db\ElementQueryInterface;

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
class VenueSearch extends Field
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
        return Craft::t('ticketmaster', 'Venue Search');
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
    public function rules(): array
    {
        return parent::rules();
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
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        return Ticketmaster::$plugin->venues->normalizeValue($this, $value, $element);
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
    public function serializeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * Returns the component’s settings HTML.
     *
     * @return string|null
     */
    public function getSettingsHtml(): ?string
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
     * @param mixed                 $value   The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                       raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return string the input HTML
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
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
    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        Ticketmaster::$plugin->venues->afterElementSave($this, $element, $isNew);
        parent::afterElementSave($element, $isNew);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        if (!Ticketmaster::$plugin) {
            return;
        }
        
        Ticketmaster::$plugin->venues->modifyElementsQuery($this, $query, $value);
    }
}
