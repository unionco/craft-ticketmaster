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

namespace unionco\ticketmaster\elements;

use Craft;
use craft\base\Element;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\elements\actions\Delete;
use unionco\ticketmaster\db\Table;
use craft\elements\actions\Restore;
use craft\elements\db\ElementQuery;
use yii\base\InvalidConfigException;
use unionco\ticketmaster\Ticketmaster;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\elements\db\EventQuery;
use unionco\ticketmaster\elements\actions\Publish;
use unionco\ticketmaster\models\Venue as VenueModel;
use unionco\ticketmaster\records\Venue as VenueRecord;
use unionco\ticketmaster\records\Event as EventRecord;

/**
 * Event Element.
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class Event extends Element
{
    // Constants
    // =========================================================================

    const STATUS_NEW = 'new';
    const STATUS_UPDATED = 'updated';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    // Public Properties
    // =========================================================================

    /**
     * @var int venueId
     */
    public $tmVenueId;

    /**
     * @var string tmEventId
     */
    public $tmEventId;

    /**
     * @var string published
     */
    public $published;

    /**
     * @var string payload
     */
    public $payload;

    /**
     * @var boolean isDirty
     */
    public $isDirty;

    /**
     * @var boolean isPublished
     */
    public $isPublished;

    /**
     * @var Venue|null
     */
    private $_venue;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string the display name of this class
     */
    public static function displayName(): string
    {
        return Craft::t('ticketmaster', 'Event');
    }

    /**
     * {@inheritdoc}
     */
    public static function refHandle()
    {
        return 'event';
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool whether elements of this type will be storing any data in the `content` table
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool whether elements of this type have traditional titles
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * Returns whether elements of this type have statuses.
     *
     * If this returns `true`, the element index template will show a Status menu
     * by default, and your elements will get status indicator icons next to them.
     *
     * Use [[statuses()]] to customize which statuses the elements might have.
     *
     * @return bool whether elements of this type have statuses
     *
     * @see statuses()
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => ['label' => Craft::t('ticketmaster', 'New'), 'color' => 'blue'],
            self::STATUS_UPDATED => ['label' => Craft::t('ticketmaster', 'Updated'), 'color' => 'pink'],
            self::STATUS_PUBLISHED => ['label' => Craft::t('ticketmaster', 'Published'), 'color' => 'yellow'],
        ];
    }

    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     *
     * The returned [[ElementQueryInterface]] instance can be further customized by calling
     * methods defined in [[ElementQueryInterface]] before `one()` or `all()` is called to return
     * populated [[ElementInterface]] instances. For example,
     *
     * ```php
     * // Find the entry whose ID is 5
     * $entry = Entry::find()->id(5)->one();
     *
     * // Find all assets and order them by their filename:
     * $assets = Asset::find()
     *     ->orderBy('filename')
     *     ->all();
     * ```
     *
     * If you want to define custom criteria parameters for your elements, you can do so by overriding
     * this method and returning a custom query class. For example,
     *
     * ```php
     * class Product extends Element
     * {
     *     public static function find()
     *     {
     *         // use ProductQuery instead of the default ElementQuery
     *         return new ProductQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * You can also set default criteria parameters on the ElementQuery if you don’t have a need for
     * a custom query class. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->limit(50);
     *     }
     * }
     * ```
     *
     * @return ElementQueryInterface the newly created [[ElementQueryInterface]] instance
     */
    public static function find(): ElementQueryInterface
    {
        return new EventQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        $elementsService = Craft::$app->getElements();

        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('ticketmaster', 'Are you sure you want to delete the selected event(s)?'),
            'successMessage' => Craft::t('ticketmaster', 'Events deleted.'),
        ]);

        $actions[] = $elementsService->createAction([
            'type' => Publish::class,
            'confirmationMessage' => Craft::t('ticketmaster', 'Are you sure you want to publish the selected event(s)?'),
            'successMessage' => Craft::t('ticketmaster', 'Events published.'),
        ]);

        // Restore
        $actions[] = $elementsService->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('ticketmaster', 'Events restored.'),
            'partialSuccessMessage' => Craft::t('ticketmaster', 'Some events restored.'),
            'failMessage' => Craft::t('ticketmaster', 'Events not restored.'),
        ]);

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title' => ['label' => Craft::t('ticketmaster', 'Title')],
            'tmEventId' => ['label' => Craft::t('ticketmaster', 'Event ID')],
            // 'venue' => ['label' => Craft::t('ticketmaster', 'Venue')],
            'dateCreated' => ['label' => Craft::t('ticketmaster', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('ticketmaster', 'Date Updated')],
        ];

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'title',
            // 'venue',
            'tmEventId',
            'dateCreated',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        if ($handle === 'venue') {
            $venue = $elements[0] ?? null;
            $this->setVenue($venue);
        } else {
            parent::setEagerLoadedElements($handle, $elements);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function prepElementQueryForTableAttribute(ElementQueryInterface $elementQuery, string $attribute)
    {
        if ($attribute === 'venue') {
            $elementQuery->andWith('venue');
        } else {
            parent::prepElementQueryForTableAttribute($elementQuery, $attribute);
        }
    }

    /**
     * Defines the sources that elements of this type may belong to.
     *
     * @param string|null $context the context ('index' or 'modal')
     *
     * @return array the sources
     *
     * @see sources()
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [];
        $sources = [
            [
                'key' => '*',
                'label' => 'All Events',
                'criteria' => [],
            ],
        ];

        $venueRecords = VenueRecord::find();
        $venues = array_map(function ($record) {
            return new VenueModel($record);
        }, $venueRecords->all());

        foreach ($venues as $key => $venue) {
            $sources[] = [
                'key' => $venue->tmVenueId,
                'label' => $venue->title,
                'data' => [
                    'type' => 'event',
                    'handle' => $venue->title,
                ],
                'criteria' => [
                    'tmVenueId' => $venue->tmVenueId,
                ],
            ];
        }

        return $sources;
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

        return array_merge($rules, [
            ['tmEventId', 'string'],
            ['tmVenueId', 'string'],
            ['payload', 'string'],
            [['tmEventId', 'tmVenueId', 'payload'], 'required'],
        ]);
    }

    public function _payload()
    {
        return Json::decode($this->payload ?? '{}');
    }

    public function _published()
    {
        return Json::decode($this->published ?? '{}');
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        $status = parent::getStatus();

        // If event id exist in ticketmaster_events table == self::STATUS_PUBLISHED
        if ($eventRecord = EventRecord::find()->where(['tmEventId' => $this->tmEventId])->one()) {
            if ($this->published && $this->isDirty) {
                return self::STATUS_UPDATED;
            }

            return self::STATUS_PUBLISHED;
        }
        // but if its published and is dirty (isDirty) then == self::STATUS_UPDATED
        // else self::STATUS_NEW

        return self::STATUS_NEW;
    }

    /**
     * {@inheritdoc}
     *
     * ---
     * ```php
     * $url = $entry->cpEditUrl;
     * ```
     * ```twig{2}
     * {% if entry.isEditable %}
     *     <a href="{{ entry.cpEditUrl }}">Edit</a>
     * {% endif %}
     * ```
     */
    public function getCpEditUrl()
    {
        // The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
        $url = UrlHelper::cpUrl('ticketmaster/events/'.$this->id.($this->tmEventId ? '-'.$this->tmEventId : ''));

        // if (Craft::$app->getIsMultiSite()) {
        //     $url .= '/' . $this->getSite()->handle;
        // }

        return $url;
    }

    /**
     * Returns the HTML for the element’s editor HUD.
     *
     * @return string The HTML for the editor HUD
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true,
            ],
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }

    /**
     * Returns the entry's venue.
     *
     * @return Venue|null
     *
     * @throws InvalidConfigException if [[venueId]] is set but invalid
     */
    public function getVenue()
    {
        if ($this->_venue !== null) {
            return $this->_venue;
        }

        if ($this->tmVenueId === null) {
            return null;
        }

        if (($this->_venue = Ticketmaster::$plugin->venues->getVenueById($this->venueId)) === null) {
            throw new InvalidConfigException('Invalid venue ID: '.$this->venueId);
        }

        return $this->_venue;
    }

    /**
     * Sets the entry's venue.
     *
     * @param Venue|null $venue
     */
    public function setVenue(VenueModel $venue = null)
    {
        $this->_venue = $venue;
    }

    /**
     * {@inheritdoc}
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'venue':
                $venue = $this->getVenue();

                return $venue ? Craft::$app->getView()->renderTemplate('_elements/element', ['element' => $venue]) : '';
        }

        return parent::tableAttributeHtml($attribute);
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert(Table::EVENT_ELEMENTS, [
                    'id' => $this->id,
                    'title' => $this->title,
                    'tmVenueId' => $this->tmVenueId,
                    'tmEventId' => $this->tmEventId,
                    'isDirty' => false,
                    'isPublished' => false,
                    'payload' => is_array($this->payload) ? Json::encode($this->payload) : $this->payload,
                    'published' => is_array($this->published) ? Json::encode($this->published) : $this->published,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update(Table::EVENT_ELEMENTS, [
                    'id' => $this->id,
                    'title' => $this->title,
                    'tmVenueId' => $this->tmVenueId,
                    'tmEventId' => $this->tmEventId,
                    'isDirty' => $this->isDirty,
                    'isPublished' => $this->isPublished,
                    'payload' => is_array($this->payload) ? Json::encode($this->payload) : $this->payload,
                    'published' => is_array($this->published) ? Json::encode($this->published) : $this->published,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     */
    public function afterDelete()
    {
    }

    /**
     * Performs actions before an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     *
     * @return bool Whether the element should be moved within the structure
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     */
    public function afterMoveInStructure(int $structureId)
    {
    }

    public function toJson()
    {
        $payload = json_decode($this->payload);

        return json_encode([
            'id' => $this->id,
            'title' => $this->title,
            'tmEventId' => $this->tmEventId,
            'payload' => $payload,
        ]);
    }
}
