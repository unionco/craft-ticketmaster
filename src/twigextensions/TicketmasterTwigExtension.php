<?php
/**
 * Union module for Craft CMS 4.x
 *
 * Private Union Site Module
 *
 * @link      https://union.co
 * @copyright Copyright (c) 2018 UNION
 */

namespace unionco\ticketmaster\twigextensions;

use Craft;
use union\app\UnionModule;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;

/**
 * @author    UNION
 * @package   UnionModule
 * @since     1.0.0
 */
class TicketmasterTwigExtension extends AbstractExtension implements GlobalsInterface
{

    // Public Properties
    // =========================================================================
    public function getGlobals(): array
    {
        return [];
    }

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
