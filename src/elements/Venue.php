<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\elements;

use Craft;

use craft\base\Element;
use craft\elements\actions\Delete;
use unionco\ticketmaster\db\Table;
use craft\elements\actions\Restore;
use unionco\ticketmaster\Ticketmaster;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\elements\db\VenueQuery;
use craft\helpers\Json;
use craft\models\FieldLayout;

/**
 * Event Element
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
 * @package   Ticketmaster
 * @since     1.0.0
 */
class Venue extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $tmVenueId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $url;

    /**
     * @var array
     */
    public $payload;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketmaster', 'Venue');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * 
     */
    public function getCpEditUrl()
    {
        return 'venues/'.$this->id;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new VenueQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title'       => Craft::t('ticketmaster', 'Title'),
            'tmVenueId'       => Craft::t('ticketmaster', 'Venue ID'),
            'dateCreated' => Craft::t('ticketmaster', 'Date Created'),
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'title',
            'tmVenueId',
            'dateCreated',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => 'All Venues',
                'criteria' => [],
            ]
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        $elementsService = Craft::$app->getElements();

        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('ticketmaster', 'Are you sure you want to delete the selected venue?'),
            'successMessage' => Craft::t('ticketmaster', 'Venues deleted.'),
        ]);

        // Restore
        $actions[] = $elementsService->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('ticketmaster', 'Venues restored.'),
            'partialSuccessMessage' => Craft::t('ticketmaster', 'Some venue restored.'),
            'failMessage' => Craft::t('ticketmaster', 'Venues not restored.'),
        ]);

        return $actions;
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
        return $rules;
    }

    public function _payload()
    {
        $payload = Json::decode($this->payload ?? '{}');

        return $payload;
    }
    
    /**
     * 
     */
    public function __toJson()
    {
        $payload = Json::decode($this->payload);
        return Json::encode([
            "id" => $this->id,
            "title" => $this->title,
            "tmVenueId" => $this->tmVenueId,
            "payload" => $payload
        ]);
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

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
                'required' => true
            ]
        ]);

        $html .= parent::getEditorHtml();

        return $html;
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
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert(Table::VENUES, [
                    'id'            => $this->id,
                    'title'         => $this->title,
                    'tmVenueId'     => $this->tmVenueId,
                    'type'          => $this->type ?? 'venue',
                    'url'           => $this->url,
                    'payload'       => is_array($this->payload) ? Json::encode($this->payload) : $this->payload,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update(Table::VENUES, [
                    'id'            => $this->id,
                    'title'         => $this->title,
                    'tmVenueId'     => $this->tmVenueId,
                    'type'          => $this->type ?? 'venue',
                    'url'           => $this->url,
                    'payload'       => is_array($this->payload) ? Json::encode($this->payload) : $this->payload,
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
        // delete events
        
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
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
     *
     * @return void
     */
    public function afterMoveInStructure(int $structureId)
    {
    }
}
