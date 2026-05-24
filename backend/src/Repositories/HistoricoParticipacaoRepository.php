<?php

namespace App\Repositories;

use App\Models\HistoricoParticipacaoModel;
use Ramsey\Uuid\Uuid;

class HistoricoParticipacaoRepository
{
    public function findByAssociacao(string $associacaoUuid)
    {
        return HistoricoParticipacaoModel::with('membro')
            ->where('associacao_uuid', $associacaoUuid)
            ->where('excluido', false)
            ->orderByDesc('data_registro')
            ->get();
    }

    public function countByMembro(string $membroUuid): int
    {
        return HistoricoParticipacaoModel::where('membro_uuid', $membroUuid)
            ->where('excluido', false)
            ->count();
    }

    public function ultimaParticipacao(string $membroUuid): ?HistoricoParticipacaoModel
    {
        return HistoricoParticipacaoModel::where('membro_uuid', $membroUuid)
            ->where('excluido', false)
            ->orderByDesc('data_registro')
            ->first();
    }

    public function create(array $data)
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        $data['excluido'] = false;
        if (!isset($data['data_registro'])) {
            $data['data_registro'] = date('Y-m-d H:i:s');
        }

        return HistoricoParticipacaoModel::create($data);
    }
}
