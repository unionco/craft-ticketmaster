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

use Throwable;
use yii\web\Response;
// use craft\helpers\StringHelper;
use craft\helpers\Json;
use craft\web\Controller;
use craft\helpers\UrlHelper;
use unionco\ticketmaster\Ticketmaster;
use unionco\ticketmaster\elements\Event;
use unionco\ticketmaster\records\Venue as VenueRecord;
use unionco\ticketmaster\models\Venue as VenueModel;

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
class EventController extends BaseController
{

    // Protected Properties
    // =========================================================================
    const BASE_URL = "ticketmaster/events";

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = false;

    public $enableCsrfValidation = false;
    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/ticketmaster/base
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $variables = [
            'title' => 'Events',
            'elementType' => 'unionco\\ticketmaster\\elements\\Event',
            'subNav' => 'events'
        ];

        return $this->renderTemplate('ticketmaster/events', $variables);
    }

    /**
     *
     */
    public function actionStoreEvent()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $eventService = Ticketmaster::$plugin->events;
        $request = Craft::$app->getRequest();
        $eventId = $request->getBodyParam('eventId');
        $venueId = $request->getBodyParam('venueId');

        $venue = VenueRecord::find()
            ->tmVenueId($venueId)
            ->one();

        if (!$venue) {
            return $this->asJson([
                'errors' => [
                    "No venue exist for id {$venueId}"
                ]
            ]);
        }

        if (!$eventId) {
            return $this->asJson([
                "success" => false,
                "error" => "Must provide an event id"
            ]);
        }

        try {
            $event = $eventService->getEventDetail((string) $eventId);
            $result = $eventService->saveEvent($event, $venue);
        } catch (Throwable $th) {
            return $this->asJson([
                "success" => false,
                "error" => $th->getMessage()
            ]);
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    /**
     *
     */
    public function actionStoreEvents()
    {
        $this->requireAcceptsJson();

        $eventService = Ticketmaster::$plugin->events;
        $request = Craft::$app->getRequest();
        $venueId = $request->getBodyParam('venueId');

        $venue = VenueRecord::findOne(['tmVenueId' => $venueId]);

        if (!$venue) {
            return $this->asJson([
                'errors' => [
                    "No venue exist for id {$venueId}"
                ]
            ]);
        }

        $venue = new VenueModel($venue);
        $events = $eventService->getEventByVenueId($venue->tmVenueId);
        foreach ($events as $key => $event) {
            $eventService->saveEvent($event, $venue);
        }

        return $this->asJson([
            "success" => true
        ]);
    }

    /**
     * Called when a user beings up an entry for editing before being displayed.
     *
     * @param string $sectionHandle The section’s handle
     * @param int|null $entryId The entry’s ID, if editing an existing entry.
     * @param int|null $draftId The entry draft’s ID, if editing an existing draft.
     * @param int|null $versionId The entry version’s ID, if editing an existing version.
     * @param string|null $siteHandle The site handle, if specified.
     * @param Entry|null $entry The entry being edited, if there were any validation errors.
     * @return Response
     * @throws NotFoundHttpException if the requested site handle is invalid
     */
    public function actionEditEvent(int $eventId = null): Response
    {
        $variables = [
            'eventId' => $eventId,
            'continueEditingUrl' => ''
        ];

        if(!$eventId) {
            $variables['event'] = new Event();
        } else {
            $variables['event'] = Event::find()
                ->id($eventId)
                ->one();
        }

        // Breadcrumbs
        $variables['crumbs'] = [
            [
                'label' => Craft::t('ticketmaster', 'Events'),
                'url' => UrlHelper::url('ticketmaster/events')
            ]
        ];

        $variables['tabs'] = [
            [
                'label' => Craft::t('ticketmaster', 'Publish'),
                'url' => '#publish',
                'id' => 'publish',
            ],
            [
                'label' => Craft::t('ticketmaster', 'Ticketmaster'),
                'url' => '#ticketmaster',
                'id' => 'ticketmaster',
            ]
        ];

        $variables['fullPageForm'] = true;
        $variables['baseCpEditUrl'] = "ticketmaster/events/{id}-{tmEventId}";
        $variables['continueEditingUrl'] = $variables['baseCpEditUrl'];
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        // var_dump($variables);die;
        return $this->renderTemplate('ticketmaster/events/_edit', $variables);
    }

    /**
     * Handle a request going to our plugin's index action URL,
     *
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $event = $this->_getEventModel();

        // Populate the category with post data
        $this->_populateEventModel($event);

        if (!Craft::$app->getElements()->saveElement($event)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $venue->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save event.'));

            // Send the event back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'event' => $event
            ]);

            return null;
        }

        if ($isPublish = $request->getBodyParam('publish')) {
            // $eventService publish this ish
            // if my event has a craftEntryId then update it
            // else create a new one
            Ticketmaster::$plugin->events->publishEvent($event);
            
            Craft::$app->getSession()->setNotice(Craft::t('app', 'Event published.'));

            return $this->redirectToPostedUrl($event);
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $event->id,
                'title' => $event->title,
                'cpEditUrl' => $event->getCpEditUrl()
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Event saved.'));

        return $this->redirectToPostedUrl($event);
    }

    /**
     *
     */
    private function _getEventModel()
    {
        $request = Craft::$app->getRequest();

        $eventId = $request->getBodyParam('eventId');
        $siteId = $request->getBodyParam('siteId');

        if ($eventId) {
            $venue = Event::find()
                ->id($eventId)
                ->one();

            if (!$venue) {
                throw new NotFoundHttpException('Venue not found');
            }
        } else {
            $venue = new Event();
            $venue->siteId = $siteId;
        }

        return $venue;
    }

    /**
     *
     */
    private function _populateEventModel(Event $event)
    {
        $request = Craft::$app->getRequest();

        $event->title = $request->getBodyParam('title', $event->title);
        $event->slug = $request->getBodyParam('slug', $event->slug);
        $event->tmVenueId = $request->getBodyParam('venueId', $event->tmVenueId);
        $event->tmEventId = $request->getBodyParam('tmEventId', $event->tmEventId);

        $event->payload = Json::encode($request->getBodyParam('fields.payload.payload', $event->_payload()));
        $event->published = Json::encode($request->getBodyParam('fields.published.payload', $event->_published()));
    }
}
