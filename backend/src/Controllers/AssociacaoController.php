<?php

namespace App\Controllers;

use App\Models\AssociacaoModel;
use App\Utils\EnderecoFormatter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Services\AssociacaoService;

class AssociacaoController
{
    protected AssociacaoService $associacaoService;

    public function __construct(AssociacaoService $associacaoService)
    {
        $this->associacaoService = $associacaoService;
    }

    public function list(Request $request, Response $response)
    {
        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $associacoes = $this->associacaoService->findAllWhere($payloadUsuarioLogado);
        $associacoesFormatadas = $associacoes->map(fn ($a) => $this->formatAssociacao($a));

        $response->getBody()->write(json_encode($associacoesFormatadas));
        return $response->withStatus(200);
    }

    public function get(Request $request, Response $response, array $args)
    {
        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $associacao = $this->associacaoService->findByUuid($args['uuid'], $payloadUsuarioLogado);
        if (!$associacao) {
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode($this->formatAssociacao($associacao)));
        return $response->withStatus(200);
    }

    public function create(Request $request, Response $response)
    {
        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $data = (array)$request->getParsedBody();
        $associacao = $this->associacaoService->create($data, $payloadUsuarioLogado);

        $response->getBody()->write(json_encode($this->formatAssociacao($associacao)));
        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $data = (array)$request->getParsedBody();
        $associacao = $this->associacaoService->update($args['uuid'], $data, $payloadUsuarioLogado);

        $response->getBody()->write(json_encode($this->formatAssociacao($associacao)));
        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $payloadUsuarioLogado = [
            'usuario_uuid' => $request->getAttribute('usuario_uuid'),
            'cargo_uuid' => $request->getAttribute('cargo_uuid'),
            'associacao_uuid' => $request->getAttribute('associacao_uuid'),
            'horta_uuid' => $request->getAttribute('horta_uuid'),
        ];

        $this->associacaoService->delete($args['uuid'], $payloadUsuarioLogado);

        $response->getBody()->write(json_encode([
            'message' => 'Registro UUID: ' . $args['uuid'] . ' excluído',
        ]));
        return $response->withStatus(200);
    }

    private function formatAssociacao(AssociacaoModel $associacao): array
    {
        $responsavel = $associacao->usuarioResponsavel;
        $administradores = $responsavel && !$responsavel->excluido
            ? [$responsavel->nome_completo]
            : [];

        return [
            'id' => $associacao->uuid,
            'nome' => $associacao->razao_social ?? $associacao->nome_fantasia ?? '-',
            'nome_fantasia' => $associacao->nome_fantasia ?? '',
            'cnpj' => $associacao->cnpj ?? '',
            'descricao' => $associacao->descricao ?? '',
            'endereco' => $this->formatEndereco($associacao),
            'telefone' => $associacao->telefone_de_contato ?? '-',
            'email' => $associacao->email ?? '-',
            'status' => (int)($associacao->status_aprovacao ?? 0) === 1 ? 'ativa' : 'inativa',
            'status_aprovacao' => (int)($associacao->status_aprovacao ?? 0),
            'administradores' => $administradores,
        ];
    }

    private function formatEndereco(AssociacaoModel $associacao): string
    {
        $endereco = null;

        if ($associacao->relationLoaded('endereco')) {
            $endereco = $associacao->getRelation('endereco');
        } elseif (!empty($associacao->endereco_uuid)) {
            $endereco = $associacao->endereco()->first();
        }

        if ($endereco instanceof \App\Models\EnderecoModel) {
            return EnderecoFormatter::format($endereco);
        }

        return '-';
    }
}
