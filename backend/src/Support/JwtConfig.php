<?php

namespace App\Support;

class JwtConfig
{
    public function __construct(
        private string $secret,
        private string $algorithm = 'HS256',
        private int $ttlInSeconds = 7200
    ) {}

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function getTtlInSeconds(): int
    {
        return $this->ttlInSeconds;
    }
}
