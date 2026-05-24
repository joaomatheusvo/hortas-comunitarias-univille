<?php

namespace App\Middlewares;

use App\Services\PermissaoDoUsuarioService;
use App\Support\JsonResponseFactory;
use App\Utils\Permissions\RoutePermissionMap;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RoutePermissionMiddleware
{
    public function __construct(
        private RoutePermissionMap $permissionsMap,
        private PermissaoDoUsuarioService $permissaoService,
        private JsonResponseFactory $jsonResponseFactory
    ) {}

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $routeIdentifier = $this->buildRouteIdentifier($path, $method);

        $publicRoutes = [
            '/sessoes/login POST',
            '/sessoes/cadastro POST',
        ];

        $usuarioUuid = $request->getAttribute('usuario_uuid');

        if (in_array($routeIdentifier, $publicRoutes, true)) {
            if (!$usuarioUuid) {
                $request = $request->withAttribute('usuario_uuid', 'NEW_ACCOUNT');
            }
            return $handler->handle($request);
        }

        if (!$usuarioUuid) {
            return $this->jsonResponseFactory->create(['error' => 'Usuário não autenticado'], 401);
        }

        $permissoesNecessarias = $this->permissionsMap->getRequiredPermissions($routeIdentifier);

        if ($permissoesNecessarias === null) {
            return $handler->handle($request);
        }

        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $permissoesDoUsuario = $this->permissaoService->findByUuid($usuarioUuid, $payloadUsuarioLogado);

        foreach ($permissoesNecessarias as $slug) {
            if (!$permissoesDoUsuario->contains('slug', $slug)) {
                return $this->jsonResponseFactory->create([
                    'error' => 'Acesso negado',
                    'missing_permission' => $slug,
                ], 403);
            }
        }

        return $handler->handle($request);
    }

    private function buildRouteIdentifier(string $path, string $method): string
    {
        $cleanPath = str_replace('/api/v1', '', $path);
        if (empty($cleanPath)) {
            $cleanPath = '/';
        }

        $uuidPattern = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i';
        $cleanPath = preg_replace($uuidPattern, '{uuid}', $cleanPath);

        return $cleanPath . ' ' . $method;
    }
}
