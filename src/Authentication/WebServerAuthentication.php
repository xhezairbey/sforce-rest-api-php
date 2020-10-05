<?php declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Xhezairi\SForce\Exception\SalesforceException;
use Xhezairi\SForce\SForce;

class WebServerAuthentication extends AbstractAuthentication implements AuthorizationInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var ServerRequestFactoryInterface
     */
    private $httpRequest;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->api->getInstanceUrl(self::OAUTH_PATH.'/authorize').'?'.http_build_query(
                [
                    'client_id'     => $this->api->getClientId(),
                    'redirect_uri'  => $this->api->getRedirectUrl(),
                    'scope'         => $this->api->getScope(),
                    'response_type' => 'code',
                ] + $options
            );
    }

    /**
     * @param string $code
     * @return array
     * @throws ClientExceptionInterface
     */
    public function requestAccessToken(): array
    {
        $response = $this->api->http->post(
            self::OAUTH_PATH.'/token',
            $this->getRequestOptions(
                [
                    'form_params' => [
                        'redirect_uri' => $this->api->getRedirectUrl(),
                        'grant_type'   => 'authorization_code',
                        'code'         => $code,
                    ],
                ]
            )
        );

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @return array
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function refreshAccessToken(): array
    {
        $response = $this->api->http->post(
            self::OAUTH_PATH.'/token',
            $this->getRequestOptions(
                [
                    'form_params' => [
                        'refresh_token' => $this->api->getRefreshToken(),
                        'grant_type'    => 'refresh_token',
                    ],
                ]
            )
        );

        if ($response->getStatusCode() !== 200) {
            throw new SalesforceException(
                "Error: call to refresh token failed with status {$response->getStatusCode()}, response: {$response->getReasonPhrase()}"
            );
        }

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Fires a void API request to check Access Token's validity
     */
    public function isAccessTokenValid(): bool
    {
        try {
            return 200 === $this->api->http->get($this->api->getBaseUrl())->getStatusCode();
        } catch (ClientExceptionInterface $e) {
            return false;
        }
    }

    /**
     * @param  array  $params
     * @return array
     */
    private function getRequestOptions(array $params): array
    {
        return [
//            RequestOptions::HEADERS     => array_merge($this->headers, $params['headers']),
            RequestOptions::FORM_PARAMS => array_merge(
                [
                    'client_id'     => $this->api->getClientId(),
                    'client_secret' => $this->api->getClientSecret(),
                ],
                $params['form_params']
            ),
        ];
    }
}
