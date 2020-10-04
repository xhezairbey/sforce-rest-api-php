<?php

declare(strict_types=1);

namespace Xhezairi\SForce;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Xhezairi\SForce\Exception\SalesforceException;

class SObject
{
    /**
     * Final part of the endpoint
     */
    public const OBJECTS_PATH = 'sobjects';

    /**
     * @var SForce
     */
    private $api;

    /**
     * SObject constructor.
     * @param  SForce  $api
     */
    public function __construct(SForce $api)
    {
//        if ( ! isset($_SESSION) and ! isset($_SESSION['salesforce'])) {
//            throw new SalesforceException('Access Denied', 403);
//        }

        $this->api = $api;
    }

    /**
     * Get a list of all available objects for the organization
     *
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function getAllObjects()
    {
        return $this->api->http->get($this->api->getBaseUrl(self::OBJECTS_PATH));
    }

    /**
     * Get metadata about an Object
     *
     * @param  string  $objectName
     * @param  bool  $all  Should this return all meta data including information about each field, URLs, and child relationships
     * @param  DateTime|null  $since  Only return metadata if it has been modified since the date provided
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function getObjectMetadata(string $objectName, $all = false, DateTime $since = null)
    {
        $headers = [];
        // Check if the If-Modified-Since header should be set
        if ($since !== null && $since instanceof DateTime) {
            $headers['IF-Modified-Since'] = $since->format('D, j M Y H:i:s e');
        } elseif ($since !== null && ! $since instanceof DateTime) {
            // If the $since flag has been set and is not a DateTime instance, throw an error
            throw new SalesforceException('To get object metadata for an object, you must provide a DateTime object');
        }

        // Should this return all meta data including information about each field, URLs, and child relationships
        $path = ($all === true) ? "/{$objectName}/describe/" : "/{$objectName}/";
        return $this->api->http->get($this->api->getBaseUrl(self::OBJECTS_PATH.$path), []);
    }

    /**
     * Get a record
     *
     * @param  string  $objectName
     * @param  string  $objectId
     * @param  array|null  $fields
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function get(string $objectName, string $objectId, array $fields = null)
    {
        $params = [];
        // If fields are included, append them to the parameters
        if ($fields !== null && is_array($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        $response = $this->api->http->get(
            $this->api->getBaseUrl(self::OBJECTS_PATH."/{$objectName}/{$objectId}"),
            [$params]
        );

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Create a new record
     *
     * @param  string  $objectName
     * @param  array  $data
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function create(string $objectName, array $data)
    {
        $url = $this->api->getBaseUrl(self::OBJECTS_PATH."/{$objectName}/");
        $request = $this->api->http->post($url, [RequestOptions::JSON => $data]);

        if ( ! in_array($request->getStatusCode(), [200, 201])) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$request->getStatusCode()}, response: {$request->getReasonPhrase()}"
            );
        }

        return json_decode((string)$request->getBody(), true); //['id']
    }

    /**
     * Update an existing object
     *
     * @param  string  $objectName
     * @param  string  $objectId
     * @param  array  $data
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function update(string $objectName, string $objectId, array $data)
    {
        $url = $this->api->getBaseUrl(self::OBJECTS_PATH."{$objectName}/{$objectId}");
        $request = $this->api->http->patch(
            $url,
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->api->getAccessToken()}",
                    'Content-type'  => 'application/json',
                ],
                [RequestOptions::JSON => $data],
            ]
        );

        $status = $request->getStatusCode();

        if ($status != 204) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $status;
    }

    /**
     * Update or Insert
     *
     * @param $objectName
     * @param $field
     * @param $id
     * @param  array  $data
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function upsert($objectName, $field, $id, array $data)
    {
        $url = $this->api->getBaseUrl(self::OBJECTS_PATH."{$objectName}/{$field}/{$id}");
        $request = $this->api->http->patch(
            $url,
            [
                'headers' => [
                    'Authorization' => "Bearer {$this->api->getAccessToken()}",
                    'Content-type'  => 'application/json',
                ],
                [RequestOptions::JSON => $data],
            ]
        );

        if ( ! in_array($request->getStatusCode(), [204, 201, 200])) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$request->getStatusCode()}, response: {$request->getReasonPhrase()}"
            );
        }

        return $request->getStatusCode();
    }

    /**
     * Delete a record
     *
     * @param  string  $objectName
     * @param  string  $objectId
     * @return mixed
     * @throws ClientExceptionInterface|SalesforceException
     */
    public function delete(string $objectName, string $objectId)
    {
        $url = $this->api->getBaseUrl(self::OBJECTS_PATH."{$objectName}/{$objectId}");
        $request = $this->api->http->delete($url);

        if ($request->getStatusCode() != 204) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$request->getStatusCode()}, response: {$request->getReasonPhrase()}"
            );
        }

        return true;
    }
}
