<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\models;

use unionco\ticketmaster\Ticketmaster;

use Craft;
use craft\base\Model;

/**
 * Ticketmaster Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Union
 * @package   Ticketmaster
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
     * @var string title
     */
    public $slug;

    /**
     * @var int venueId
     */
    public $venueId;

    /**
     * @var string tmEventId
     */
    public $tmEventId;

    /**
     * @var int craftEntryId
     */
    public $craftEntryId;

    /**
     * @var string url
     */
    public $url;

    /**
     * @var string url
     */
    public $payload;

    // Public Methods
    // =========================================================================

    /**
     * 
     */
    public function __toJson()
    {
        $payload = Json::decode($this->payload);
        return Json::encode([
            "id" => $this->id,
            "title" => $this->title,
            "venueId" => $this->venueId,
            "tmEventId" => $this->tmEventId,
            "payload" => $payload
        ]);
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
        $rules[] = [['title', 'venueId', 'tmEventId', 'payload'], 'required'];
        return $rules;
    }
}
