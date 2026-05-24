<?php

namespace App\Controllers;

use App\Services\MembroAssociacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MembroAssociacaoController
{
    protected MembroAssociacaoService $service;

    public function __construct(MembroAssociacaoService $service)
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

    private function formatarMembro($membro): array
    {
        return [
            'id' => $membro->uuid,
            'associacao_id' => $membro->associacao_uuid,
            'nome' => $membro->nome,
            'email' => $membro->email,
            'telefone' => $membro->telefone,
            'observacoes' => $membro->observacoes,
            'status' => $membro->status,
        ];
    }

    public function list(Request $request, Response $response, array $args)
    {
        $membros = $this->service->listar($args['uuid']);
        $formatados = $membros->map(fn($m) => $this->formatarMembro($m))->values();
        $response->getBody()->write(json_encode($formatados));
        return $response->withStatus(200);
    }

    public function get(Request $request, Response $response, array $args)
    {
        $membro = $this->service->obter($args['uuid'], $args['membroUuid']);
        if (!$membro) {
            $response->getBody()->write(json_encode(['error' => 'Membro não encontrado']));
            return $response->withStatus(404);
        }
        $response->getBody()->write(json_encode($this->formatarMembro($membro)));
        return $response->withStatus(200);
    }

    public function create(Request $request, Response $response, array $args)
    {
        $data = (array)$request->getParsedBody();
        $membro = $this->service->criar($args['uuid'], $data, $this->payload($request));
        $response->getBody()->write(json_encode($this->formatarMembro($membro)));
        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $data = (array)$request->getParsedBody();
        $membro = $this->service->atualizar($args['uuid'], $args['membroUuid'], $data, $this->payload($request));
        $response->getBody()->write(json_encode($this->formatarMembro($membro)));
        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $this->service->excluir($args['uuid'], $args['membroUuid']);
        $response->getBody()->write(json_encode(['message' => 'Membro excluído com sucesso']));
        return $response->withStatus(200);
    }
}
