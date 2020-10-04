<?php declare(strict_types=1);

namespace Xhezairi\SForce;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Logger;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Xhezairi\SForce\Exception\SalesforceException;

/**
 * The Salesforce REST API PHP Wrapper
 *
 * This class connects to the Salesforce REST API and performs actions on that API
 * @link https://github.com/jahumes/salesforce-rest-api-php-wrapper
 * @link https://github.com/bjsmasth/php-salesforce-rest-api
 */
class SForce
{
    use HasToken;

    /**
     * Final part of the endpoint
     */
    private const DATA_PATH = '/services/data';

    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;

    /** @var string As configured in the Connected App, used as Web Authorization Flow callback */
    private $redirectUrl;

    /** @var string Base URI will be used in conjunction with a REST resource endpoint */
    private $baseUrl;
    /**
     * @var mixed
     */
    private $instanceUrl;

    /**
     * @var string
     */
    public $apiVersion = '/v49.0/';

    /**
     * @var string
     */
    private $scope = 'api+web+refresh_token';

    /**
     * @var string
     */
    private $grant_type = 'code';

    /**
     * @var ClientInterface
     */
    public $http;

    /**
     * @var HandlerStack
     */
    private $stack;

    /**
     * @var string[]
     */
    protected $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json; charset=utf-8',
    ];

    /**
     * @var null
     */
    public $auth = null;

    /**
     * Set up the Salesforce API and instantiate all default variables
     *
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->clientId = $config['clientId'];
        $this->clientSecret = $config['clientSecret'];
        $this->redirectUrl = $config['redirectUrl'];
        $this->instanceUrl = $config['instanceUrl'];
        $this->baseUrl = self::DATA_PATH.$this->apiVersion;

        $this->stack = new HandlerStack();
        $this->stack->setHandler(new CurlHandler());
        $this->stack->push($this->handleAuthorizationHeader());
//        $this->stack->push($this->handleLoggingRequestsMiddleware('{method} {uri} HTTP/{version} {req_body}'));
//        $this->stack->push($this->handleLoggingRequestsMiddleware('RESPONSE: {code} - {res_body}'));

        $this->http = new Client([
            'debug' => true,
            'handler' => $this->stack,
            'headers' => $this->headers,
            'base_uri' => $this->instanceUrl,
        ]);
    }

    /**
     * @return mixed
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @param  string|null  $endpoint
     * @return string
     */
    public function getBaseUrl(string $endpoint = null): string
    {
        return isset($endpoint) ? $this->baseUrl.$endpoint : $this->baseUrl;
    }

    /**
     * @param  string|null  $endpoint
     * @return string
     */
    public function getInstanceUrl(string $endpoint = null): string
    {
        return isset($endpoint) ? $this->instanceUrl.$endpoint : $this->instanceUrl;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Get a list of all the API Versions for the instance
     *
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function getAPIVersions()
    {
        return json_decode((string) $this->http->get(self::DATA_PATH)->getBody(), true);
    }

    /**
     * Gets a list of all the available REST endpoints
     *
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function getAvailableResources()
    {
        return json_decode((string) $this->http->get($this->getBaseUrl())->getBody(), true);
    }

    /**
     * Lists the limits for the organization.
     *
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function getOrgLimits()
    {
        return json_decode((string) $this->http->get($this->getBaseUrl('limits'))->getBody(), true);
    }

    /**
     * @param $query
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function getQueryFromUrl($query)
    {
        return json_decode((string) $this->http->get($this->getBaseUrl($query))->getBody(), true);
    }

    /**
     * Handle Authorization Header
     */
    private function handleAuthorizationHeader()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $request->withHeader('Authorization', 'Bearer '.$this->accessToken);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * @param  string  $messageFormat
     * @return callable
     */
    private function handleLoggingRequestsMiddleware(string $messageFormat)
    {
        return Middleware::log(
            new Logger('api-consumer'),
            new MessageFormatter($messageFormat) // '{req_body} - {res_body}'
        );
    }

    /**
     * @param  string  $method
     * @param  string  $url
     * @param  array  $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException|SalesforceException
     */
    public function request(string $method, string $url, array $options)
    {
        $request = $this->http->request($method, $this->api->getBaseUrl($url), $options);

        if ( ! in_array($request->getStatusCode(), [200, 201])) {
            throw new SalesforceException(
            "Error: call to URL {$url} failed with status {$request->getStatusCode()}, response: {$request->getReasonPhrase()}"
            );
        }

        return json_decode((string)$request->getBody(), true);
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
//        if (array_key_exists($name, $this->_config)) {
//            return $this->_config[$name];
//        }

        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    /**
     * @param $property
     * @param $value
     * @return $this
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }
}
