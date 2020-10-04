<?php declare(strict_types=1);

namespace Xhezairi\SForce;

use Psr\Http\Client\ClientExceptionInterface;
use Xhezairi\SForce\Exception\SalesforceException;

class SQuery
{
    /**
     * @var SForce
     */
    private $api;

    /**
     * SQuery constructor.
     * @param  SForce  $api
     */
    public function __construct(SForce $api)
    {
        $this->api = $api;
    }

    /**
     * @param  string $query
     * @return array
     * @throws ClientExceptionInterface
     */
    public function query(string $query): array
    {
        $request = $this->api->http->get(
            $this->api->getBaseUrl('query'),
            [
                'query' => [
                    'q' => $query,
                ],
            ]
        );

        return json_decode((string)$request->getBody(), true);
    }

    /**
     * Searches using a SOQL Query
     *
     * @param  string  $query  The query to perform
     * @param  bool  $all  Search through deleted and merged data as well
     * @param  bool  $explain  If the explain flag is set, it will return feedback on the query performance
     * @return array
     * @throws ClientExceptionInterface
     */
    public function searchSOQL(string $query, bool $all = false, bool $explain = false): array
    {
        $data = [
            'q' => $query,
        ];

        // If the explain flag is set, it will return feedback on the query performance
        if ($explain) {
            $data['explain'] = $data['q'];
            unset($data['q']);
        }

        // If all, search through deleted and merged data as well
        $path = $all ? 'queryAll/' : 'query/';

        $response = $this->api->http->get($path, $data);

        return json_decode((string)$response->getBody(), true);
    }
}
