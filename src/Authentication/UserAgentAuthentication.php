<?php declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use Xhezairi\SForce\Exception\SalesforceException;
use Xhezairi\SForce\SForce;

class UserAgentAuthentication extends AbstractAuthentication implements AuthorizationInterface
{
    public function requestAccessToken(): array
    {
        // TODO: Implement requestAccessToken() method.
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->api->getInstanceUrl(self::OAUTH_PATH.'/authorize').'?'.http_build_query(
                [
                    'client_id'     => $this->api->getClientId(),
                    'redirect_uri'  => $this->api->getRedirectUrl(),
                    'response_type' => 'token',

                ] + $options
            );
    }
}
