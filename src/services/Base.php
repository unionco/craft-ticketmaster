<?php
/**
 * Ticketmaster plugin for Craft CMS 3.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use GuzzleHttp\Client as RestClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use unionco\ticketmaster\Ticketmaster;

/**
 * Base Service
 *
 * @author    Union
 * @package   Ticketmaster
 * @since     1.0.0
 */
class Base extends Component
{
    // Public Properties
    // =========================================================================
    
    /**
     * @var guzzle $api
     */
    const BASEURL = 'https://app.ticketmaster.com';
    
    /**
     * @var guzzle $api
     */
    public $baseQuery;
    
    /**
     * @var guzzle $api
     */
    protected $api;

    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->baseQuery = ["apikey" => Ticketmaster::$plugin->getSettings()->getConsumerKey()];
        $this->api = new RestClient([
            "base_uri" => static::BASEURL,
            "query" => $this->baseQuery
        ]);
    }

    /**
     * Create Guzzle Request
     *
     * @param string $method GET|POST
     * @param string $uri
     * @param array $params
     *
     * @return mixed $response
     */
    public function makeRequest(string $method, string $uri, array $params = [])
    {
        $response = null;
        try {
            if (isset($params['query'])) {
                $params['query'] = array_merge($this->baseQuery, $params['query']);
            }

            $request = $this->api->request(
                $method,
                $uri,
                $params
            );

            $response = $request->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                echo Psr7\str($e->getRequest());
                echo Psr7\str($e->getResponse());
            }
        }

        return $response ? Json::decode($response) : null;
    }
}
