<?php declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Xhezairi\SForce\Exception\SalesforceException;
use Xhezairi\SForce\SForce;

abstract class AbstractAuthentication
{
    /**
     * REST Authentication endpoint
     */
    public const OAUTH_PATH = '/services/oauth2';

    /**
     * @var SForce
     */
    public $api;

    /**
     * @note Leaving these here for now as a reminder to myself in the future
     * to maybe consider making the library PSR HTTP compliant.
     * @var ClientInterface $httpClient
     * @var ServerRequestFactoryInterface $httpRequest
     * @var array $headers
     */
    private $httpClient, $httpRequest, $headers;

    /**
     * AbstractAuthentication constructor.
     * @param  SForce  $api
     * @throws SalesforceException
     */
    public function __construct(SForce $api)
    {
        if (!$api->getClientId() || !$api->getInstanceUrl()) {
            throw new SalesforceException('Salesforce API: Missing Instance URL.');
        }

        $this->api = $api;
//        $this->headers = [
//            'Accept'        => 'application/json',
//            'Authorization' => 'Basic '.base64_encode($this->api->getClientId().':'.$this->api->getClientSecret()),
//            'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
//        ];
    }

    /**
     * @param  array  $params
     * @return array
     */
    protected function getRequestOptions(array $params): array
    {
        return [
            'headers'     => array_merge($this->headers, $params['headers']),
            RequestOptions::FORM_PARAMS => array_merge(
                [
                    'client_id'     => $this->api->getClientId(),
                    'client_secret' => $this->api->getClientSecret(),
                ],
                $params['form_params']
            ),
        ];
    }

    /**
     * Zero ill intent and completely harmless API call which
     * is intended to fail if the current Access Token has expired.
     * @return bool
     */
    public function isAccessTokenValid(): bool
    {
        try {
            return 200 === $this->api->http->get($this->api->getBaseUrl('userinfo'))->getStatusCode();
        } catch (ClientExceptionInterface $e) {
            return false;
        }
    }

    /**
     * API call to request an access_token
     *
     * @return array
     */
    abstract public function requestAccessToken(): array;
}
