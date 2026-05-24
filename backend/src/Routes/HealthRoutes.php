<?php

use App\Controllers\HealthController;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $app) {
    $app->get('/health', HealthController::class . ':check');
};
