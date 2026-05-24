<?php

namespace App\Services;

use App\Repositories\AssociacaoRepository;
use App\Repositories\HistoricoParticipacaoRepository;
use App\Repositories\MembroAssociacaoRepository;
use Exception;

class EngajamentoAssociacaoService
{
    protected MembroAssociacaoRepository $membroRepository;
    protected HistoricoParticipacaoRepository $historicoRepository;
    protected AssociacaoRepository $associacaoRepository;

    public function __construct(
        MembroAssociacaoRepository $membroRepository,
        HistoricoParticipacaoRepository $historicoRepository,
        AssociacaoRepository $associacaoRepository
    ) {
        $this->membroRepository = $membroRepository;
        $this->historicoRepository = $historicoRepository;
        $this->associacaoRepository = $associacaoRepository;
    }

    private function calcularNivel(int $participacoes, string $statusMembro): string
    {
        if ($statusMembro === 'inativo') {
            return 'inativo';
        }
        if ($participacoes >= 5) {
            return 'alto';
        }
        if ($participacoes >= 2) {
            return 'medio';
        }
        return 'baixo';
    }

    public function listarEngajamento(string $associacaoUuid): array
    {
        $associacao = $this->associacaoRepository->findByUuid($associacaoUuid);
        if (!$associacao || $associacao->excluido) {
            throw new Exception('Associação não encontrada');
        }

        $membros = $this->membroRepository->findByAssociacao($associacaoUuid);
        $resultado = [];

        foreach ($membros as $membro) {
            $total = $this->historicoRepository->countByMembro($membro->uuid);
            $ultima = $this->historicoRepository->ultimaParticipacao($membro->uuid);

            $resultado[] = [
                'membro_id' => $membro->uuid,
                'nome' => $membro->nome,
                'status' => $membro->status,
                'total_participacoes' => $total,
                'ultima_participacao' => $ultima?->data_registro?->format('Y-m-d H:i:s'),
                'nivel_engajamento' => $this->calcularNivel($total, $membro->status),
            ];
        }

        usort($resultado, fn($a, $b) => $b['total_participacoes'] <=> $a['total_participacoes']);

        return $resultado;
    }

    public function listarHistorico(string $associacaoUuid)
    {
        $associacao = $this->associacaoRepository->findByUuid($associacaoUuid);
        if (!$associacao || $associacao->excluido) {
            throw new Exception('Associação não encontrada');
        }

        return $this->historicoRepository->findByAssociacao($associacaoUuid);
    }
}
