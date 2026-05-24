<?php

namespace App\Controllers;

use App\Services\EngajamentoAssociacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EngajamentoAssociacaoController
{
    protected EngajamentoAssociacaoService $service;

    public function __construct(EngajamentoAssociacaoService $service)
    {
        $this->service = $service;
    }

    public function engajamento(Request $request, Response $response, array $args)
    {
        $dados = $this->service->listarEngajamento($args['uuid']);
        $response->getBody()->write(json_encode($dados));
        return $response->withStatus(200);
    }

    public function historico(Request $request, Response $response, array $args)
    {
        $registros = $this->service->listarHistorico($args['uuid']);
        $formatados = $registros->map(function ($h) {
            return [
                'id' => $h->uuid,
                'membro_id' => $h->membro_uuid,
                'membro_nome' => $h->membro?->nome,
                'tarefa_id' => $h->tarefa_uuid,
                'descricao' => $h->descricao,
                'data_registro' => $h->data_registro instanceof \DateTimeInterface
                    ? $h->data_registro->format('Y-m-d H:i:s')
                    : $h->data_registro,
            ];
        })->values();
        $response->getBody()->write(json_encode($formatados));
        return $response->withStatus(200);
    }
}
