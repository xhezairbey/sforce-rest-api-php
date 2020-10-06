<?php declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Xhezairi\SForce\Exception\SalesforceAuthenticationException;
use Xhezairi\SForce\Exception\SalesforceException;
use Xhezairi\SForce\SForce;

class WebServerAuthentication extends AbstractAuthentication implements AuthorizationInterface
{
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
                        'code'         => $_GET['code'],
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
            throw new SalesforceAuthenticationException(
                "Error: call to refresh token failed with status {$response->getStatusCode()}, response: {$response->getReasonPhrase()}"
            );
        }

        return json_decode((string)$response->getBody(), true);
    }
}
