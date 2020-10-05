<?php

namespace Xhezairi\SForce\Authentication;

interface AuthorizationInterface
{
    /**
     * Browser-based URL to authorize use of a client with a Connected App
     *
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string;
}
