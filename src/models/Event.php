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

use Adbar\Dot;
use Craft;
use craft\base\Model;
use craft\helpers\Json;
use unionco\ticketmaster\models\TicketmasterEvent;
use unionco\ticketmaster\Ticketmaster;

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
class Event extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string title
     */
    public $id;

    /**
     * @var string title
     */
    public $title;

    /**
     * @var string tmEventId
     */
    public $tmEventId;

    public $payload;
    public $ownerId;
    public $ownerSiteId;
    public $fieldId;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    private $_doc;

    // Public Methods
    // =========================================================================

    public function toJson()
    {
        $payload = Json::decode($this->payload);
        // $published = json_decode($this->published);

        return Json::encode([
            'id' => $this->id,
            'title' => $this->title,
            'tmEventId' => $this->tmEventId,
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
        $rules[] = [['title', 'tmEventId', 'payload'], 'required'];

        return $rules;
    }
}
