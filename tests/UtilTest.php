<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testSanitizeSpaydValueLength()
    {
        $input = 'Támpa Baý Club';
        $sanitized = Util::sanitizeSpaydValue($input, 13);
        $this->assertEquals(13, strlen($sanitized));
        $this->assertEquals('Támpa Baý C', $sanitized);
    }

    public function testSanitizeSpaydValueUppercase()
    {
        $input = 'Támpa Baý Club';
        $sanitized = Util::sanitizeSpaydValue($input, 35, true);
        $this->assertEquals('TÁMPA BAÝ CLUB', $sanitized);
    }
}
