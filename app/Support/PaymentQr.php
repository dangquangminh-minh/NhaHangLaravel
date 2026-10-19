<?php

namespace App\Support;

use App\Models\Order;

class PaymentQr
{
    private static function field(string $tag, string $value): string
    {
        return $tag.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    public static function payload(Order $order): string
    {
        $bank = self::field('00', (string) config('restaurant.bank_bin')).self::field('01', (string) config('restaurant.bank_account'));
        $provider = self::field('00', 'A000000727').self::field('01', $bank).self::field('02', 'QRIBFTTA');
        $payload = self::field('00', '01').self::field('01', '12').self::field('38', $provider).self::field('53', '704').self::field('54', Money::plain($order->total)).self::field('58', 'VN').self::field('62', self::field('08', 'THANHTOAN '.$order->id)).'6304';

        return $payload.self::crc16($payload);
    }

    public static function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $crc ^= ord($payload[$i]) << 8;
            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
