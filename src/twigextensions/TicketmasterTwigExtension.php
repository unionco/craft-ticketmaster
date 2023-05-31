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
use craft\web\twig\Extension;
use union\app\UnionModule;

/**
 * @author    UNION
 * @package   UnionModule
 * @since     1.0.0
 */
class TicketmasterTwigExtension extends Extension
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
}
