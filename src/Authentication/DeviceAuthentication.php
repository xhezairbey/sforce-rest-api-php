<?php declare(strict_types=1);

namespace Xhezairi\SForce\Authentication;

use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Xhezairi\SForce\Exception\SalesforceAuthenticationException;

class DeviceAuthentication extends AbstractAuthentication
{
    /**
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceAuthenticationException
     */
    public function requestAuthorization(): array
    {
//        POST /services/oauth2/token  HTTP/1.1
//        Host: login.salesforce.com
//        Content-Type: application/x-www-form-urlencoded
//
//        response_type=device_code&
//        client_id=3MVG9PhR6g6B7ps7TTI4cP7Mppg3l7tu.​MRAYULyVqcmA9hGLpHiiS.Q7rO9yjlmffiBUM6tFpYAlXEkRjHb9&scope=api
        /*
         * The request header contains the following information:
           - The Salesforce OAuth 2.0 token endpoint. Connected apps send OAuth token requests to this endpoint.
           - The authorization server.
           - Content type of the request.
         */
        $response = $this->api->http->post(
            self::OAUTH_PATH.'/token',
            [
                RequestOptions::JSON => [
                    'client_id'      => $this->api->getClientId(),
                    'response_type'  => 'device_code',
                ]
            ]
        );

        /*
         {
            "device_code":"M01WRzlQaFI2ZzZCN3BzN1RUSTRj...",
            "user_code":"X1D9SEET",
            "verification_uri":"https://acme.my.salesforce.com/connect",
            "interval":5
         }
         */
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @return array
     * @throws ClientExceptionInterface|SalesforceAuthenticationException
     * @uses application/x-www-form-urlencoded
     */
    public function requestAccessToken(): array
    {
        /*
            POST /services/oauth2/token  HTTP/1.1
            Host: login.salesforce.com
            Content-Type: application/x-www-form-urlencoded

            grant_type=device&
            client_id=3MVG9PhR6g6B7ps7TTI4cP7Mppg3l7tu.MRAYULyVqcmA9hGLpHiiS.​Q7rO9yjlmffiBUM6tFpYAlXEkRjHb9
            code=M01WRzlQaFI2ZzZCN3BzN1RUSTRjUDdNcHBnM2w3dHUu​TVJBWVVMeVZxY21BOWhHTHBIaWlTLlE3ck85eWpsbWZmaUJVTTZ0RnBZQWxYRWtSakhiOTsxMC4yMi4zNC45MjsxNDc3N​jc0NDg3NTA1O1gxRDlTRUVU
         */

        // response
//        {
//            "access_token": "00DD00000008Uw2!ARkAQGppKf6n.VwG.EnFSvi731qWh.7vKfaJjL7h49yutIC84gAsxM​rqcE81GjpTjQbDLkytl2ZwosNbIJwUS0X8ahiILj3e"
//            "refresh_token": "your token here"
//            "signature": "hJuYICd2IHsjyTcFqTYiOr8THmgDmrcjgWaMp13X6dY="
//            "scope": "api"
//            "instance_url": "https://yourInstance.salesforce.com"
//            "id": "https://login.salesforce.com/id/00DD00000008Uw2MAE/005D0000001cAGmIAM"
//            "token_type": "Bearer"
//            "issued_at": "1477674717112"
//        }

        if (!isset($body['access_token'])) {
            throw new SalesforceAuthenticationException(
                "Error: call to access token request failed with status {$response->getStatusCode()}, response: {$response->getReasonPhrase()}"
            );
        }

        return $body['access_token'];
    }
}
