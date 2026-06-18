<?php

namespace App\Utils;

use App\Models\EnderecoModel;

class EnderecoFormatter
{
    public static function format(?EnderecoModel $endereco): string
    {
        if (!$endereco) {
            return '-';
        }

        $logradouro = trim(($endereco->tipo_logradouro ?? '') . ' ' . ($endereco->logradouro ?? ''));
        $partes = array_filter([
            $logradouro !== '' ? $logradouro : null,
            $endereco->numero ? 'nº ' . $endereco->numero : null,
            !empty($endereco->complemento) ? $endereco->complemento : null,
            !empty($endereco->bairro) ? $endereco->bairro : null,
            self::formatCidadeEstado($endereco->cidade ?? null, $endereco->estado ?? null),
            !empty($endereco->cep) ? 'CEP ' . self::formatCep($endereco->cep) : null,
        ]);

        return $partes ? implode(', ', $partes) : '-';
    }

    private static function formatCidadeEstado(?string $cidade, ?string $estado): ?string
    {
        if ($cidade && $estado) {
            return $cidade . '/' . $estado;
        }

        return $cidade ?: ($estado ?: null);
    }

    private static function formatCep(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep);
        if (strlen($digits) === 8) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }

        return $cep;
    }
}
