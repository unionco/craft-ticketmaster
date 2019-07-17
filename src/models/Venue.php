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

namespace unionco\ticketmaster\models;

use unionco\ticketmaster\Ticketmaster;
use Craft;
use craft\base\Model;

/**
 * Ticketmaster Settings Model.
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Union
 *
 * @since     1.0.0
 */
class Venue extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string title
     */
    public $id;

    public $ownerId;
    public $ownerSiteId;
    public $fieldId;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    /**
     * @var string title
     */
    public $title;

    /**
     * @var int venueId
     */
    public $tmVenueId;

    /**
     * @var string url
     */
    public $payload;

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
        $rules[] = [['title', 'tmVenueId', 'ownerId', 'ownerSiteId', 'fieldId', 'payload'], 'required'];

        return $rules;
    }

    public function toJson()
    {
        $payload = json_decode($this->payload);

        return json_encode([
            'title' => $this->title,
            'tmVenueId' => $this->tmVenueId,
            'payload' => $payload,
        ]);
    }

    /**
     * Helper method to reach any ticketmaster field
     */
    public function tm(string $handle = null)
    {
        if (is_null($this->_doc)) {
            $this->_doc = new Dot(Json::decode($this->payload));
        }

        if (!$handle) {
            return $this->_doc->all();
        }

        return $this->_doc->get($handle);
    }
}
