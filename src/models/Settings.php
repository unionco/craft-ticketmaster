<?php
/**
 * Ticketmaster plugin for Craft CMS 4.x
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
use craft\behaviors\EnvAttributeParserBehavior;

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
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string consumerKey
     */
    public $consumerKey;

    /**
     * @var string consumerSecrety
     */
    public $consumerSecret;

    /**
     * @var string section
     */
    public $section;

    /**
     * @var boolean enableWhenPublish
     */
    public $enableWhenPublish;
    
    /**
     * @var string section
     */
    public $sectionEntryType;
    
    /**
     * @var array sectionMap
     */
    public $sectionMap;

    /**
     * @var array sectionMap
     */
    public $apiFields = [];

    // Public Methods
    // =========================================================================

    /**
     * Returns the site’s base URL.
     *
     * @return string|null
     */
    public function getConsumerKey()
    {
        if ($this->consumerKey) {
            return Craft::parseEnv($this->consumerKey);
        }

        return null;
    }

    /**
     * Returns the site’s base URL.
     *
     * @return string|null
     */
    public function getConsumerSecret()
    {
        if ($this->consumerSecret) {
            return Craft::parseEnv($this->consumerSecret);
        }

        return null;
    }

    /**
     * 
     */
    public function getSectionMapField($handle)
    {
        $index = array_search($handle, array_column($this->sectionMap, 'section'));
        if ($index !== false) {
            return $this->sectionMap[$index];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => [
                    'consumerKey',
                    'consumerSecret'
                ],
            ]
        ];
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
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['consumerKey', 'consumerSecret'], 'required'];
        return $rules;
    }
}
