<?php

namespace App\Controllers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HealthController
{
    public function check(Request $request, Response $response): Response
    {
        $status = 'ok';
        $database = 'ok';

        try {
            Capsule::connection()->select('SELECT 1');
        } catch (\Throwable $e) {
            $status = 'degraded';
            $database = 'unavailable';
        }

        $body = [
            'status' => $status,
            'database' => $database,
            'modulo' => 'associacao',
            'timestamp' => date('c'),
        ];

        $response->getBody()->write(json_encode($body));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status === 'ok' ? 200 : 503);
    }
}
