<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\controllers;

use Craft;

use craft\web\Controller;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Venue;

/**
 * Base Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class VenueController extends BaseController
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = false;

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/ticketmaster/base
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $variables = [
            'title' => 'Venues',
            'elementType' => 'unionco\\ticketmaster\\elements\\Venue',
            'subNav' => 'venues'
        ];

        return $this->renderTemplate('ticketmaster/elements', $variables);
    }

    /**
     * Handle a request going to our plugin's index action URL,
     *
     * @return mixed
     */
    public function actionNew()
    {
        $venue = new Venue();

        $variables = [
            'venue' => $venue,
            'isNew' => true
        ];

        $variables['tabs'] = [
            [ "label" => Craft::t('ticketmaster', 'General'), 'url' => '#tab-general' ],
            [ "label" => Craft::t('ticketmaster', 'Details'), 'url' => '#tab-details' ],
        ];

        return $this->renderTemplate('ticketmaster/venue/_edit', $variables);
    }

    /**
     * Handle a request going to our plugin's index action URL,
     *
     * @return mixed
     */
    public function actionEdit(int $id)
    {
        $venue = Venue::find()
            ->id($id)
            ->one();
        
        $variables = [
            'venue' => $venue,
            'isNew' => false,
        ];

        // Define the content tabs
        // ---------------------------------------------------------------------

        $variables['tabs'] = [
            [ "label" => Craft::t('ticketmaster', 'General'), 'url' => '#tab-general' ],
            [ "label" => Craft::t('ticketmaster', 'Details'), 'url' => '#tab-details' ],
        ];

        return $this->renderTemplate('ticketmaster/venue/_edit', $variables);
    }

    /**
     * Handle a request going to our plugin's index action URL,
     *
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $venue = $this->_getVenueModel();
        $request = Craft::$app->getRequest();

        // Populate the category with post data
        $this->_populateVenueModel($venue);

        if (!Craft::$app->getElements()->saveElement($venue)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $venue->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save venue.'));

            // Send the venue back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'venue' => $venue
            ]);

            return null;
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $venue->id,
                'title' => $venue->title,
                'status' => $venue->getStatus(),
                'cpEditUrl' => $venue->getCpEditUrl()
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Venue saved.'));

        return $this->redirectToPostedUrl($venue);
    }

    /**
     * 
     */
    private function _getVenueModel(): Venue
    {
        $request = Craft::$app->getRequest();

        $venueId = $request->getBodyParam('venueId');
        $siteId = $request->getBodyParam('siteId');

        if ($venueId) {
            $venue = Venue::find()
                ->id($venueId)
                ->one();

            if (!$venue) {
                throw new NotFoundHttpException('Venue not found');
            }
        } else {
            $venue = new Venue();
            $venue->siteId = $siteId;
        }

        return $venue;
    }

    /**
     * 
     */
    private function _populateVenueModel(Venue $venue)
    {
        $request = Craft::$app->getRequest();

        $venue->title = $request->getBodyParam('title', $venue->title);
        $venue->tmVenueId = $request->getBodyParam('fields.tmVenueId', $venue->tmVenueId);
        
        $payload = $request->getBodyParam('fields.payload');
        if ($payload) {
            $venue->type = "venue";
            $venue->url = $payload['url'];
            $venue->payload = $payload;
        }
    }
}