<?php

use DI\ContainerBuilder;
use App\Models\MembroAssociacaoModel;
use App\Models\TarefaAssociacaoModel;
use App\Models\HistoricoParticipacaoModel;
use App\Repositories\MembroAssociacaoRepository;
use App\Repositories\TarefaAssociacaoRepository;
use App\Repositories\HistoricoParticipacaoRepository;
use App\Services\MembroAssociacaoService;
use App\Services\TarefaAssociacaoService;
use App\Services\EngajamentoAssociacaoService;
use App\Controllers\MembroAssociacaoController;
use App\Controllers\TarefaAssociacaoController;
use App\Controllers\EngajamentoAssociacaoController;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        MembroAssociacaoModel::class => DI\autowire(MembroAssociacaoModel::class),
        TarefaAssociacaoModel::class => DI\autowire(TarefaAssociacaoModel::class),
        HistoricoParticipacaoModel::class => DI\autowire(HistoricoParticipacaoModel::class),
        MembroAssociacaoRepository::class => DI\autowire(MembroAssociacaoRepository::class),
        TarefaAssociacaoRepository::class => DI\autowire(TarefaAssociacaoRepository::class),
        HistoricoParticipacaoRepository::class => DI\autowire(HistoricoParticipacaoRepository::class),
        MembroAssociacaoService::class => DI\autowire(MembroAssociacaoService::class),
        TarefaAssociacaoService::class => DI\autowire(TarefaAssociacaoService::class),
        EngajamentoAssociacaoService::class => DI\autowire(EngajamentoAssociacaoService::class),
        MembroAssociacaoController::class => DI\autowire(MembroAssociacaoController::class),
        TarefaAssociacaoController::class => DI\autowire(TarefaAssociacaoController::class),
        EngajamentoAssociacaoController::class => DI\autowire(EngajamentoAssociacaoController::class),
    ]);
};
