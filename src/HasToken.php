<?php declare(strict_types=1);

namespace Xhezairi\SForce;

trait HasToken
{
    /**
     * @var
     */
    private $accessToken;
    /**
     * @var mixed|string
     */
    private $refreshToken;

    /**
     * @return String
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param  string  $token
     */
    public function setAccessToken(string $token)
    {
        $this->accessToken = $token;
    }

    /**
     * @return String
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param  string  $token
     */
    public function setRefreshToken(string $token)
    {
        $this->refreshToken = $token;
    }
}
