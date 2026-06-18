<?php

namespace App\Support;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory as SlimResponseFactory;

class JsonResponseFactory
{
    private SlimResponseFactory $responseFactory;

    public function __construct()
    {
        $this->responseFactory = new SlimResponseFactory();
    }

    public function create(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
