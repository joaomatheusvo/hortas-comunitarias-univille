<?php

use App\Controllers\AssociacaoController;
use App\Controllers\MembroAssociacaoController;
use App\Controllers\TarefaAssociacaoController;
use App\Controllers\EngajamentoAssociacaoController;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $app) {
    $app->group('/associacoes', function (RouteCollectorProxy $group) {
        $group->get('', AssociacaoController::class . ':list');
        $group->post('', AssociacaoController::class . ':create');

        // Gestão da associação (membros, tarefas, participação)
        $group->get('/{uuid}/membros', MembroAssociacaoController::class . ':list');
        $group->get('/{uuid}/membros/{membroUuid}', MembroAssociacaoController::class . ':get');
        $group->post('/{uuid}/membros', MembroAssociacaoController::class . ':create');
        $group->put('/{uuid}/membros/{membroUuid}', MembroAssociacaoController::class . ':update');
        $group->delete('/{uuid}/membros/{membroUuid}', MembroAssociacaoController::class . ':delete');

        $group->get('/{uuid}/tarefas', TarefaAssociacaoController::class . ':list');
        $group->post('/{uuid}/tarefas', TarefaAssociacaoController::class . ':create');
        $group->put('/{uuid}/tarefas/{tarefaUuid}', TarefaAssociacaoController::class . ':update');
        $group->post('/{uuid}/tarefas/{tarefaUuid}/concluir', TarefaAssociacaoController::class . ':concluir');
        $group->delete('/{uuid}/tarefas/{tarefaUuid}', TarefaAssociacaoController::class . ':delete');

        $group->get('/{uuid}/historico-participacao', EngajamentoAssociacaoController::class . ':historico');
        $group->get('/{uuid}/engajamento', EngajamentoAssociacaoController::class . ':engajamento');

        $group->get('/{uuid}', AssociacaoController::class . ':get');
        $group->put('/{uuid}', AssociacaoController::class . ':update');
        $group->delete('/{uuid}', AssociacaoController::class . ':delete');
    });
};
