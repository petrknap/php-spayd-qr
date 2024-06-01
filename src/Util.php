<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

class Util
{
    public static function sanitizeSpaydValue(string $string, int $maxLength, bool $toUppercase = false): string
    {
        if ($toUppercase) {
            $string = mb_strtoupper($string, 'UTF-8');
        }

        return mb_strcut($string, 0, $maxLength, 'UTF-8');
    }
}
