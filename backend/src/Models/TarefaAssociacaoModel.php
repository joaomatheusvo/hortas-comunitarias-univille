<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarefaAssociacaoModel extends Model
{
    protected $table = 'tarefas_associacao';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;
    const CREATED_AT = 'data_de_criacao';
    const UPDATED_AT = 'data_de_ultima_alteracao';

    protected $fillable = [
        'uuid',
        'associacao_uuid',
        'titulo',
        'descricao',
        'membro_responsavel_uuid',
        'status',
        'data_conclusao',
        'excluido',
        'usuario_criador_uuid',
        'usuario_alterador_uuid',
    ];

    protected $casts = [
        'excluido' => 'boolean',
        'data_conclusao' => 'datetime',
    ];

    public function associacao()
    {
        return $this->belongsTo(AssociacaoModel::class, 'associacao_uuid', 'uuid');
    }

    public function membroResponsavel()
    {
        return $this->belongsTo(MembroAssociacaoModel::class, 'membro_responsavel_uuid', 'uuid');
    }
}
