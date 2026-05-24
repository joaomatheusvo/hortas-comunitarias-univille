<?php

namespace App\Support;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JwtService
{
    public function __construct(private JwtConfig $config) {}

    public function encode(array $payload): string
    {
        return JWT::encode(
            $payload,
            $this->config->getSecret(),
            $this->config->getAlgorithm()
        );
    }

    public function decode(string $token): stdClass
    {
        return JWT::decode(
            $token,
            new Key($this->config->getSecret(), $this->config->getAlgorithm())
        );
    }

    public function getTtlInSeconds(): int
    {
        return $this->config->getTtlInSeconds();
    }
}
