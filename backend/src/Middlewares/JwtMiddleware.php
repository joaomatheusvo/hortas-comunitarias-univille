<?php

namespace App\Middlewares;

use App\Support\JsonResponseFactory;
use App\Support\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JwtMiddleware
{
    public function __construct(
        private JwtService $jwtService,
        private JsonResponseFactory $jsonResponseFactory
    ) {}

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        $publicRoutes = [
            '/api/v1/sessoes/login POST',
            '/api/v1/sessoes/cadastro POST',
            '/api/v1/health GET',
        ];

        $routeKey = $uri . ' ' . $method;

        if (in_array($routeKey, $publicRoutes, true)) {
            $usuarioUuid = $request->getAttribute('usuario_uuid');
            if (!$usuarioUuid) {
                $request = $request->withAttribute('usuario_uuid', 'NEW_ACCOUNT');
            }
            return $handler->handle($request);
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->jsonResponseFactory->create(['error' => 'Token ausente'], 401);
        }

        $token = $matches[1];

        try {
            $decoded = $this->jwtService->decode($token);

            $request = $request
                ->withAttribute('usuario_uuid', $decoded->usuario_uuid ?? null)
                ->withAttribute('cargo_uuid', $decoded->cargo_uuid ?? null)
                ->withAttribute('associacao_uuid', $decoded->associacao_uuid ?? null)
                ->withAttribute('horta_uuid', $decoded->horta_uuid ?? null);
        } catch (\Exception $e) {
            return $this->jsonResponseFactory->create([
                'error' => 'Token inválido',
                'message' => $e->getMessage(),
            ], 401);
        }

        return $handler->handle($request);
    }
}
