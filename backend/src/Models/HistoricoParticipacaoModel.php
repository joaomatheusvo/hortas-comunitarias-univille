<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoParticipacaoModel extends Model
{
    protected $table = 'historico_participacao';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'associacao_uuid',
        'membro_uuid',
        'tarefa_uuid',
        'descricao',
        'data_registro',
        'excluido',
        'usuario_criador_uuid',
    ];

    protected $casts = [
        'excluido' => 'boolean',
        'data_registro' => 'datetime',
    ];

    public function membro()
    {
        return $this->belongsTo(MembroAssociacaoModel::class, 'membro_uuid', 'uuid');
    }

    public function tarefa()
    {
        return $this->belongsTo(TarefaAssociacaoModel::class, 'tarefa_uuid', 'uuid');
    }
}
