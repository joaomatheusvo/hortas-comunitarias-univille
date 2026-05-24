<?php

namespace App\Services;

use App\Repositories\AssociacaoRepository;
use App\Repositories\HistoricoParticipacaoRepository;
use App\Repositories\MembroAssociacaoRepository;
use App\Repositories\TarefaAssociacaoRepository;
use App\Utils\AssociacaoGestaoValidator;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;

class TarefaAssociacaoService
{
    protected TarefaAssociacaoRepository $tarefaRepository;
    protected MembroAssociacaoRepository $membroRepository;
    protected HistoricoParticipacaoRepository $historicoRepository;
    protected AssociacaoRepository $associacaoRepository;

    public function __construct(
        TarefaAssociacaoRepository $tarefaRepository,
        MembroAssociacaoRepository $membroRepository,
        HistoricoParticipacaoRepository $historicoRepository,
        AssociacaoRepository $associacaoRepository
    ) {
        $this->tarefaRepository = $tarefaRepository;
        $this->membroRepository = $membroRepository;
        $this->historicoRepository = $historicoRepository;
        $this->associacaoRepository = $associacaoRepository;
    }

    private function validarAssociacao(string $associacaoUuid): void
    {
        $associacao = $this->associacaoRepository->findByUuid($associacaoUuid);
        if (!$associacao || $associacao->excluido) {
            throw new Exception('Associação não encontrada');
        }
    }

    private function validarMembroResponsavel(string $associacaoUuid, ?string $membroUuid): void
    {
        if (!$membroUuid) {
            return;
        }
        $membro = $this->membroRepository->findByUuid($membroUuid, $associacaoUuid);
        if (!$membro) {
            throw new Exception('Membro responsável não encontrado nesta associação');
        }
        if ($membro->status !== 'ativo') {
            throw new Exception('Membro responsável deve estar ativo');
        }
    }

    public function listar(string $associacaoUuid)
    {
        $this->validarAssociacao($associacaoUuid);
        return $this->tarefaRepository->findByAssociacao($associacaoUuid);
    }

    public function criar(string $associacaoUuid, array $data, array $payloadUsuarioLogado)
    {
        $this->validarAssociacao($associacaoUuid);

        $titulo = AssociacaoGestaoValidator::texto($data['titulo'] ?? '', 'Título da tarefa');
        $descricao = AssociacaoGestaoValidator::textoOpcional($data['descricao'] ?? null, 1000);
        $membroUuid = $data['membro_responsavel_uuid'] ?? $data['membro_responsavel_id'] ?? null;
        $this->validarMembroResponsavel($associacaoUuid, $membroUuid);

        return $this->tarefaRepository->create([
            'associacao_uuid' => $associacaoUuid,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'membro_responsavel_uuid' => $membroUuid,
            'status' => 'pendente',
            'usuario_criador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
        ]);
    }

    public function atualizar(string $associacaoUuid, string $tarefaUuid, array $data, array $payloadUsuarioLogado)
    {
        $this->validarAssociacao($associacaoUuid);

        if (isset($data['titulo'])) {
            $data['titulo'] = AssociacaoGestaoValidator::texto($data['titulo'], 'Título da tarefa');
        }
        if (array_key_exists('descricao', $data)) {
            $data['descricao'] = AssociacaoGestaoValidator::textoOpcional($data['descricao'], 1000);
        }

        $membroUuid = $data['membro_responsavel_uuid'] ?? $data['membro_responsavel_id'] ?? null;
        if ($membroUuid !== null) {
            $this->validarMembroResponsavel($associacaoUuid, $membroUuid);
        }

        $update = array_filter([
            'titulo' => $data['titulo'] ?? null,
            'descricao' => $data['descricao'] ?? null,
            'membro_responsavel_uuid' => $membroUuid,
            'usuario_alterador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
        ], fn($v) => $v !== null);

        $tarefa = $this->tarefaRepository->update($tarefaUuid, $associacaoUuid, $update);
        if (!$tarefa) {
            throw new Exception('Tarefa não encontrada');
        }
        return $tarefa;
    }

    public function concluir(string $associacaoUuid, string $tarefaUuid, array $payloadUsuarioLogado)
    {
        $this->validarAssociacao($associacaoUuid);

        $tarefa = $this->tarefaRepository->findByUuid($tarefaUuid, $associacaoUuid);
        if (!$tarefa) {
            throw new Exception('Tarefa não encontrada');
        }
        if ($tarefa->status === 'concluida') {
            throw new Exception('Tarefa já está concluída');
        }

        return DB::connection()->transaction(function () use ($associacaoUuid, $tarefaUuid, $tarefa, $payloadUsuarioLogado) {
            $tarefaAtualizada = $this->tarefaRepository->update($tarefaUuid, $associacaoUuid, [
                'status' => 'concluida',
                'data_conclusao' => date('Y-m-d H:i:s'),
                'usuario_alterador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
            ]);

            if ($tarefaAtualizada->membro_responsavel_uuid) {
                $this->historicoRepository->create([
                    'associacao_uuid' => $associacaoUuid,
                    'membro_uuid' => $tarefaAtualizada->membro_responsavel_uuid,
                    'tarefa_uuid' => $tarefaUuid,
                    'descricao' => 'Conclusão da tarefa: ' . $tarefaAtualizada->titulo,
                    'usuario_criador_uuid' => $payloadUsuarioLogado['usuario_uuid'],
                ]);
            }

            return $tarefaAtualizada;
        });
    }

    public function excluir(string $associacaoUuid, string $tarefaUuid): void
    {
        $this->validarAssociacao($associacaoUuid);
        if (!$this->tarefaRepository->delete($tarefaUuid, $associacaoUuid)) {
            throw new Exception('Tarefa não encontrada');
        }
    }
}
