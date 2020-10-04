<?php

namespace Xhezairi\SForce\Authentication;

final class AuthOptions
{
    /**
     * @uses \Xhezairi\SForce\Authentication\DeviceAuthentication
     */
    public const DEVICE_FLOW = 'device';

    /**
     * @uses \Xhezairi\SForce\Authentication\JwtBearerAuthentication
     */
    public const JWT_BEARER_FLOW = 'jwt_bearer';

    /**
     * @uses \Xhezairi\SForce\Authentication\UserAgentAuthentication
     */
    public const USER_AGENT_FLOW = 'user_agent';

    /**
     * @uses \Xhezairi\SForce\Authentication\UserPassAuthentication
     */
    public const USER_PASS_FLOW = 'user_pass';

    /**
     * OAuth 2.0 Web Server Flow for Web App Integration
     *
     * To integrate an external web app with the Salesforce API,
     * use the OAuth 2.0 web server flow, which implements the OAuth 2.0 authorization code grant type.
     * With this flow, the server hosting the web app must be able to protect the connected app’s identity,
     * defined by the client ID and client secret.
     *
     * @uses \Xhezairi\SForce\Authentication\WebServerAuthentication
     * @link https://help.salesforce.com/articleView?id=remoteaccess_oauth_web_server_flow.htm&type=5
     */
    public const WEB_SERVER_FLOW = 'web_server';
}
