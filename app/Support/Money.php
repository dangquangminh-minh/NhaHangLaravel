<?php

namespace App\Support;

class Money
{
    public static function cents(string|int $amount): int
    {
        [$whole,$fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function plain(string|int|float|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }
        [$whole,$fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');
        $fraction = rtrim($fraction, '0');

        return $whole.($fraction !== '' ? '.'.$fraction : '');
    }

    public static function format(string|int|float|null $amount): string
    {
        $plain = self::plain($amount);
        if ($plain === '') {
            return '';
        }
        [$whole,$fraction] = array_pad(explode('.', $plain, 2), 2, '');
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole);

        return $whole.($fraction !== '' ? ','.$fraction : '');
    }
}
