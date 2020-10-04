<?php

namespace Xhezairi\SForce;

use Xhezairi\SForce\Exception\SalesforceException;

class SQuery
{
    public function __construct()
    {
    }

    public function query($query)
    {
        $url = "{$this->instance_url}/services/data/v39.0/query";
        $request = $this->http->get($url, [
            'headers' => [
                'Authorization' => "OAuth {$this->accessToken}"
            ],
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    /**
     * Searches using a SOQL Query
     *
     * @param  string  $query  The query to perform
     * @param  bool  $all  Search through deleted and merged data as well
     * @param  bool  $explain  If the explain flag is set, it will return feedback on the query performance
     * @return mixed
     * @throws SalesforceException
     */
    public function searchSOQL(string $query, $all = false, $explain = false)
    {
        $search_data = [
            'q' => $query,
        ];

        // If the explain flag is set, it will return feedback on the query performance
        if ($explain) {
            $search_data['explain'] = $search_data['q'];
            unset($search_data['q']);
        }

        // If all, search through deleted and merged data as well
        $path = $all ? 'queryAll/' : 'query/';

        return $this->request($path, $search_data, self::METH_GET);
    }
}
