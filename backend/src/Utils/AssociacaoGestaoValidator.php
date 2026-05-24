<?php

namespace App\Utils;

use Exception;

class AssociacaoGestaoValidator
{
    public static function texto(string $valor, string $campo, int $max = 255): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw new Exception("{$campo} é obrigatório");
        }
        if (mb_strlen($valor) > $max) {
            throw new Exception("{$campo} deve ter no máximo {$max} caracteres");
        }
        return $valor;
    }

    public static function textoOpcional(?string $valor, int $max = 255): ?string
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }
        $valor = trim($valor);
        if (mb_strlen($valor) > $max) {
            throw new Exception("Texto deve ter no máximo {$max} caracteres");
        }
        return $valor;
    }

    public static function emailOpcional(?string $email): ?string
    {
        if ($email === null || trim($email) === '') {
            return null;
        }
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido');
        }
        return $email;
    }

    public static function statusMembro(string $status): string
    {
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            throw new Exception('Status deve ser ativo ou inativo');
        }
        return $status;
    }
}
