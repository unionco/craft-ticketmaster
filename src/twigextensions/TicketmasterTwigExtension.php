<?php
/**
 * Union module for Craft CMS 3.x
 *
 * Private Union Site Module
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2018 UNION
 */

namespace unionco\ticketmaster\twigextensions;

use Craft;

use DateTime;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use union\app\UnionModule;
use craft\helpers\UrlHelper;

/**
 * @author    UNION
 * @package   UnionModule
 * @since     1.0.0
 */
class TicketmasterTwigExtension extends Twig_Extension
{

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Ticketmaster';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('get', [$this, 'get']),
            new Twig_SimpleFilter('isArray', 'is_array'),
        ];
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     * @return array
     */
    public function getFunctions()
    {
        return [];
    }

    /**
     * 
     */
    public function get($object, $path)
    {
        return '';
    }
}
