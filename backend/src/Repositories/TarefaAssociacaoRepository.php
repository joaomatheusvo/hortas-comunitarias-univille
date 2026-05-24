<?php

namespace App\Repositories;

use App\Models\TarefaAssociacaoModel;
use Ramsey\Uuid\Uuid;

class TarefaAssociacaoRepository
{
    public function findByAssociacao(string $associacaoUuid)
    {
        return TarefaAssociacaoModel::with('membroResponsavel')
            ->where('associacao_uuid', $associacaoUuid)
            ->where('excluido', false)
            ->orderByDesc('data_de_criacao')
            ->get();
    }

    public function findByUuid(string $uuid, string $associacaoUuid)
    {
        return TarefaAssociacaoModel::with('membroResponsavel')
            ->where('uuid', $uuid)
            ->where('associacao_uuid', $associacaoUuid)
            ->where('excluido', false)
            ->first();
    }

    public function create(array $data)
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        $data['excluido'] = false;
        if (!isset($data['status'])) {
            $data['status'] = 'pendente';
        }

        return TarefaAssociacaoModel::create($data);
    }

    public function update(string $uuid, string $associacaoUuid, array $data)
    {
        $tarefa = $this->findByUuid($uuid, $associacaoUuid);
        if (!$tarefa) {
            return null;
        }
        $tarefa->update($data);
        return $tarefa->fresh(['membroResponsavel']);
    }

    public function delete(string $uuid, string $associacaoUuid): bool
    {
        $tarefa = $this->findByUuid($uuid, $associacaoUuid);
        if (!$tarefa) {
            return false;
        }
        $tarefa->excluido = true;
        $tarefa->save();
        return true;
    }
}
