<?php

use App\Middlewares\JwtMiddleware;
use App\Middlewares\RoutePermissionMiddleware;
use App\Services\PermissaoDoUsuarioService;
use App\Support\JsonResponseFactory;
use App\Support\JwtConfig;
use App\Support\JwtService;
use App\Utils\Permissions\RoutePermissionMap;

return function (\DI\ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        JwtConfig::class => function () {
            return new JwtConfig(
                $_ENV['JWT_SECRET'] ?? '',
                $_ENV['JWT_ALGORITHM'] ?? 'HS256',
                (int)($_ENV['JWT_TTL'] ?? 7200)
            );
        },

        JwtService::class => \DI\autowire(JwtService::class),

        JsonResponseFactory::class => \DI\create(JsonResponseFactory::class),

        JwtMiddleware::class => \DI\autowire(JwtMiddleware::class),

        RoutePermissionMap::class => \DI\create(RoutePermissionMap::class),

        RoutePermissionMiddleware::class => \DI\autowire(RoutePermissionMiddleware::class)
            ->constructor(
                \DI\get(RoutePermissionMap::class),
                \DI\get(PermissaoDoUsuarioService::class),
                \DI\get(JsonResponseFactory::class)
            ),
    ]);
};
