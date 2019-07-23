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
use Twig_Extension;
use Twig_SimpleFilter;
use union\app\UnionModule;

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
        return [];
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
}
