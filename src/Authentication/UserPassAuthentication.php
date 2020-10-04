<?php

namespace Xhezairi\SForce\Authentication;

use Xhezairi\SForce\Exception\SalesforceAuthenticationException;
use GuzzleHttp\Client;
use Xhezairi\SForce\Exception\SalesforceException;
use Xhezairi\SForce\HasToken;

/**
 * The username-password flow generates access tokens as Salesforce Session IDs that can’t be introspected.
 * This flow doesn't support scopes or refresh tokens.
 */
class UserPassAuthentication
{

}
