<?php

namespace PetrKnap\Php\SpaydQr\Test;

use Endroid\QrCode\QrCode;
use PetrKnap\Php\SpaydQr\SpaydQr;
use PHPUnit\Framework\TestCase;
use Shoptet\Spayd\Spayd;

class SpaydQrTest extends TestCase
{
    const IBAN = 'CZ7801000000000000000123';
    const AMOUNT = 799.50;
    const CURRENCY = 'CZK';
    const SIZE = 64;

    public function testFactoryWorks()
    {
        $spaydQr = SpaydQr::create(
            static::IBAN,
            static::AMOUNT,
            static::CURRENCY,
            static::SIZE
        );

        $this->assertInstanceOf(SpaydQr::class, $spaydQr);
        $this->assertEquals(
            'SPD*1.0*ACC:CZ7801000000000000000123*AM:799.50*CC:CZK*CRC32:8a0f48b6',
            $spaydQr->getSpayd()->generate()
        );
        $this->assertEquals(
            static::SIZE,
            $spaydQr->getQrCode()->getSize()
        );
    }

    public function testSetVariableSymbolWorks()
    {
        $spayd = $this->getMockBuilder(Spayd::class)
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();
        $spayd->expects($this->exactly(4))
            ->method('add')
            ->willReturnSelf();
        $spayd->expects($this->at(3))
            ->method('add')
            ->with(SpaydQr::VARIABLE_SYMBOL, 123)
            ->willReturnSelf();

        $this->getSpaydQr($spayd, null)->setVariableSymbol(123);
    }

    public function testGetQrCodeContentWorks()
    {
        $expectedSpayd = 'Expected SPAYD';
        $expectedQrCode = 'Expected QR code';

        $spayd = $this->getMockBuilder(Spayd::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $spayd->expects($this->once())
            ->method('generate')
            ->willReturn($expectedSpayd);

        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['setText', 'writeString'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('setText')
            ->with($expectedSpayd);
        $qrCode->expects($this->once())
            ->method('writeString')
            ->willReturn($expectedQrCode);

        $this->assertEquals(
            $expectedQrCode,
            $this->getSpaydQr($spayd, $qrCode)->getQrCodeContent()
        );
    }

    private function getSpaydQr(?Spayd $spayd, ?QrCode $qrCode): SpaydQr
    {
        return new SpaydQr(
            $spayd ?: new Spayd(),
            $qrCode ?: new QrCode(),
            static::IBAN,
            static::AMOUNT,
            static::CURRENCY,
            static::SIZE
        );
    }
}
