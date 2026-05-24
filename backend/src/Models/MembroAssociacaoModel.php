<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembroAssociacaoModel extends Model
{
    protected $table = 'membros_associacao';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;
    const CREATED_AT = 'data_de_criacao';
    const UPDATED_AT = 'data_de_ultima_alteracao';

    protected $fillable = [
        'uuid',
        'associacao_uuid',
        'nome',
        'email',
        'telefone',
        'observacoes',
        'status',
        'excluido',
        'usuario_criador_uuid',
        'usuario_alterador_uuid',
    ];

    protected $casts = [
        'excluido' => 'boolean',
    ];

    public function associacao()
    {
        return $this->belongsTo(AssociacaoModel::class, 'associacao_uuid', 'uuid');
    }

    public function tarefas()
    {
        return $this->hasMany(TarefaAssociacaoModel::class, 'membro_responsavel_uuid', 'uuid');
    }
}
