<?php

namespace App\Controllers;

use App\Services\TarefaAssociacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TarefaAssociacaoController
{
    protected TarefaAssociacaoService $service;

    public function __construct(TarefaAssociacaoService $service)
    {
        $this->service = $service;
    }

    private function payload(Request $request): array
    {
        return [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];
    }

    private function formatarTarefa($tarefa): array
    {
        $membro = $tarefa->membroResponsavel;
        return [
            'id' => $tarefa->uuid,
            'associacao_id' => $tarefa->associacao_uuid,
            'titulo' => $tarefa->titulo,
            'descricao' => $tarefa->descricao,
            'status' => $tarefa->status,
            'membro_responsavel_id' => $tarefa->membro_responsavel_uuid,
            'membro_responsavel_nome' => $membro?->nome,
            'data_conclusao' => $tarefa->data_conclusao?->format('Y-m-d H:i:s'),
        ];
    }

    public function list(Request $request, Response $response, array $args)
    {
        $tarefas = $this->service->listar($args['uuid']);
        $formatadas = $tarefas->map(fn($t) => $this->formatarTarefa($t))->values();
        $response->getBody()->write(json_encode($formatadas));
        return $response->withStatus(200);
    }

    public function create(Request $request, Response $response, array $args)
    {
        $data = (array)$request->getParsedBody();
        $tarefa = $this->service->criar($args['uuid'], $data, $this->payload($request));
        $tarefa->load('membroResponsavel');
        $response->getBody()->write(json_encode($this->formatarTarefa($tarefa)));
        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $data = (array)$request->getParsedBody();
        $tarefa = $this->service->atualizar($args['uuid'], $args['tarefaUuid'], $data, $this->payload($request));
        $response->getBody()->write(json_encode($this->formatarTarefa($tarefa)));
        return $response->withStatus(200);
    }

    public function concluir(Request $request, Response $response, array $args)
    {
        $tarefa = $this->service->concluir($args['uuid'], $args['tarefaUuid'], $this->payload($request));
        $response->getBody()->write(json_encode($this->formatarTarefa($tarefa)));
        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $this->service->excluir($args['uuid'], $args['tarefaUuid']);
        $response->getBody()->write(json_encode(['message' => 'Tarefa excluída com sucesso']));
        return $response->withStatus(200);
    }
}
