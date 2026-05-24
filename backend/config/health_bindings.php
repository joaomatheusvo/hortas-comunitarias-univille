<?php

use DI\ContainerBuilder;
use App\Controllers\HealthController;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        HealthController::class => DI\autowire(HealthController::class),
    ]);
};
