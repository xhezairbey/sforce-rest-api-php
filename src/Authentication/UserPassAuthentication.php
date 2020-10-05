<?php
declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Xhezairi\SForce\Exception\SalesforceAuthenticationException;

/**
 * The username-password flow generates access tokens as Salesforce Session IDs that can’t be introspected.
 * This flow doesn't support scopes or refresh tokens.
 */
class UserPassAuthentication extends AbstractAuthentication
{
    /**
     * @inheritDoc
     * @return array
     * @throws ClientExceptionInterface|SalesforceAuthenticationException
     */
    public function requestAccessToken(): array
    {
        $response = $this->api->http->post(
            self::OAUTH_PATH.'/token',
            [
                RequestOptions::JSON => [
                    'client_id'     => $this->api->getClientId(),
                    'client_secret' => $this->api->getClientSecret(),
                    'redirect_uri'  => $this->api->getRedirectUrl(),
                    'grant_type'    => 'password',
                    'username'      => $this->api->username,
                    'password'      => $this->api->password, //.$secretToken
                ]
            ]
        );

        $body = json_decode((string)$response->getBody(), true);

        if (!isset($body['access_token'])) {
            throw new SalesforceAuthenticationException(
                "Error: call to access token request failed with status {$response->getStatusCode()}, response: {$response->getReasonPhrase()}"
            );
        }

        return $body['access_token'];
    }
}
