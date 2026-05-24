<?php

namespace App\Repositories;

use App\Models\MembroAssociacaoModel;
use Ramsey\Uuid\Uuid;

class MembroAssociacaoRepository
{
    public function findByAssociacao(string $associacaoUuid)
    {
        return MembroAssociacaoModel::where('associacao_uuid', $associacaoUuid)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get();
    }

    public function findByUuid(string $uuid, string $associacaoUuid)
    {
        return MembroAssociacaoModel::where('uuid', $uuid)
            ->where('associacao_uuid', $associacaoUuid)
            ->where('excluido', false)
            ->first();
    }

    public function create(array $data)
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        $data['excluido'] = false;
        if (!isset($data['status'])) {
            $data['status'] = 'ativo';
        }

        return MembroAssociacaoModel::create($data);
    }

    public function update(string $uuid, string $associacaoUuid, array $data)
    {
        $membro = $this->findByUuid($uuid, $associacaoUuid);
        if (!$membro) {
            return null;
        }
        $membro->update($data);
        return $membro->fresh();
    }

    public function delete(string $uuid, string $associacaoUuid): bool
    {
        $membro = $this->findByUuid($uuid, $associacaoUuid);
        if (!$membro) {
            return false;
        }
        $membro->excluido = true;
        $membro->save();
        return true;
    }
}
