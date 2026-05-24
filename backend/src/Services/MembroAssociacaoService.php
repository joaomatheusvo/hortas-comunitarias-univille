<?php

namespace App\Services;

use App\Repositories\AssociacaoRepository;
use App\Repositories\MembroAssociacaoRepository;
use App\Utils\AssociacaoGestaoValidator;
use Exception;

class MembroAssociacaoService
{
    protected MembroAssociacaoRepository $membroRepository;
    protected AssociacaoRepository $associacaoRepository;

    public function __construct(
        MembroAssociacaoRepository $membroRepository,
        AssociacaoRepository $associacaoRepository
    ) {
        $this->membroRepository = $membroRepository;
        $this->associacaoRepository = $associacaoRepository;
    }

    private function validarAssociacao(string $associacaoUuid): void
    {
        $associacao = $this->associacaoRepository->findByUuid($associacaoUuid);
        if (!$associacao || $associacao->excluido) {
            throw new Exception('Associação não encontrada');
        }
    }

    public function listar(string $associacaoUuid)
    {
        $this->validarAssociacao($associacaoUuid);
        return $this->membroRepository->findByAssociacao($associacaoUuid);
    }

    public function obter(string $associacaoUuid, string $membroUuid)
    {
        $this->validarAssociacao($associacaoUuid);
        return $this->membroRepository->findByUuid($membroUuid, $associacaoUuid);
    }

    public function criar(string $associacaoUuid, array $data, array $payloadUsuarioLogado)
    {
        $this->validarAssociacao($associacaoUuid);

        $nome = AssociacaoGestaoValidator::texto($data['nome'] ?? '', 'Nome');
        $email = AssociacaoGestaoValidator::emailOpcional($data['email'] ?? null);
        $telefone = AssociacaoGestaoValidator::textoOpcional($data['telefone'] ?? null, 30);
        $observacoes = AssociacaoGestaoValidator::textoOpcional($data['observacoes'] ?? null, 1000);
        $status = AssociacaoGestaoValidator::statusMembro($data['status'] ?? 'ativo');

        return $this->membroRepository->create([
            'associacao_uuid' => $associacaoUuid,
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'observacoes' => $observacoes,
            'status' => $status,
            'usuario_criador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
        ]);
    }

    public function atualizar(string $associacaoUuid, string $membroUuid, array $data, array $payloadUsuarioLogado)
    {
        $this->validarAssociacao($associacaoUuid);

        if (isset($data['status'])) {
            $data['status'] = AssociacaoGestaoValidator::statusMembro($data['status']);
        }
        if (isset($data['nome'])) {
            $data['nome'] = AssociacaoGestaoValidator::texto($data['nome'], 'Nome');
        }
        if (array_key_exists('email', $data)) {
            $data['email'] = AssociacaoGestaoValidator::emailOpcional($data['email']);
        }
        if (array_key_exists('telefone', $data)) {
            $data['telefone'] = AssociacaoGestaoValidator::textoOpcional($data['telefone'], 30);
        }
        if (array_key_exists('observacoes', $data)) {
            $data['observacoes'] = AssociacaoGestaoValidator::textoOpcional($data['observacoes'], 1000);
        }

        $update = array_filter([
            'nome' => $data['nome'] ?? null,
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'status' => $data['status'] ?? null,
            'usuario_alterador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
        ], fn($v) => $v !== null);

        $membro = $this->membroRepository->update($membroUuid, $associacaoUuid, $update);
        if (!$membro) {
            throw new Exception('Membro não encontrado');
        }
        return $membro;
    }

    public function excluir(string $associacaoUuid, string $membroUuid): void
    {
        $this->validarAssociacao($associacaoUuid);
        if (!$this->membroRepository->delete($membroUuid, $associacaoUuid)) {
            throw new Exception('Membro não encontrado');
        }
    }
}
