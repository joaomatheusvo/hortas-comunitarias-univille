<?php

namespace App\Utils;

class CnpjValidator
{
    public static function normalize(string $cnpj): string
    {
        $digits = preg_replace('/\D/', '', $cnpj);

        if (!self::isValid($digits)) {
            throw new \InvalidArgumentException('CNPJ inválido');
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 3),
            substr($digits, 5, 3),
            substr($digits, 8, 4),
            substr($digits, 12, 2)
        );
    }

    public static function isValid(string $cnpj): bool
    {
        $digits = preg_replace('/\D/', '', $cnpj);

        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits)) {
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $digit1 = self::calculateDigit(substr($digits, 0, 12), $weights1);
        $digit2 = self::calculateDigit(substr($digits, 0, 12) . $digit1, $weights2);

        return substr($digits, -2) === (string) $digit1 . (string) $digit2;
    }

    private static function calculateDigit(string $base, array $weights): int
    {
        $sum = 0;
        foreach (str_split($base) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
