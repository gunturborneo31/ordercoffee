<?php

namespace App\Services;

class QrisService
{
    /**
     * Generate QRIS string with embedded nominal using EMVCo modification.
     * Inserts Tag 54 (Transaction Amount) and recalculates CRC-16/CCITT-FALSE.
     */
    public function generateWithAmount(string $staticQris, int $amount): string
    {
        // Normalize payload and remove full CRC segment (6304 + 4 hex)
        $normalized = preg_replace('/\s+/', '', trim($staticQris)) ?? '';
        $qrisWithoutCrc = preg_replace('/6304[0-9A-F]{4}$/i', '', $normalized) ?? $normalized;
        $qrisWithoutCrc = preg_replace('/6304$/', '', $qrisWithoutCrc) ?? $qrisWithoutCrc;

        if ($qrisWithoutCrc === '') {
            throw new \InvalidArgumentException('Payload QRIS statis kosong atau tidak valid.');
        }

        // Change tag 01 (point-of-initiation) from 11 (static) to 12 (dynamic) so amount is embedded
        $qrisWithoutCrc = preg_replace('/010211/', '010212', $qrisWithoutCrc, 1) ?? $qrisWithoutCrc;

        // Build tag 54 (Transaction Amount)
        $amountStr = (string) $amount;
        $tag54 = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

        // Insert tag 54 before tag 58 (Country Code)
        if (str_contains($qrisWithoutCrc, '5802ID')) {
            $qrisWithoutCrc = str_replace('5802ID', $tag54 . '5802ID', $qrisWithoutCrc);
        } else {
            // Append before CRC position
            $qrisWithoutCrc .= $tag54;
        }

        // Recalculate CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF)
        $crc = $this->crc16($qrisWithoutCrc . '6304');

        return $qrisWithoutCrc . '6304' . strtoupper($crc);
    }

    /**
     * Calculate CRC-16/CCITT-FALSE.
     */
    private function crc16(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);

        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
                $crc &= 0xFFFF;
            }
        }

        return str_pad(dechex($crc), 4, '0', STR_PAD_LEFT);
    }
}
