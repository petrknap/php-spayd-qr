<?php

namespace PetrKnap\Php\SpaydQr\Test;

use Endroid\QrCode\QrCode;
use Money\Money;
use PetrKnap\Php\SpaydQr\SpaydQr;
use PHPUnit\Framework\TestCase;
use Shoptet\Spayd\Spayd;

class SpaydQrTest extends TestCase
{
    const IBAN = 'CZ7801000000000000000123';

    public function testFactoryWorks()
    {
        $spaydQr = SpaydQr::create(
            static::IBAN,
            Money::CZK(79950)
        );

        $this->assertInstanceOf(SpaydQr::class, $spaydQr);
        $this->assertEquals(
            'SPD*1.0*ACC:CZ7801000000000000000123*AM:799.50*CC:CZK*CRC32:8a0f48b6',
            $spaydQr->getSpayd()->generate()
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
            ->with(SpaydQr::SPAYD_VARIABLE_SYMBOL, 123)
            ->willReturnSelf();

        $this->getSpaydQr($spayd, null)->setVariableSymbol(123);
    }

    public function testGetContentTypeWorks()
    {
        $expectedContentType = 'Expected content type';

        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContentType'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('getContentType')
            ->willReturn($expectedContentType);

        $this->assertEquals(
            $expectedContentType,
            $this->getSpaydQr(null, $qrCode)->getContentType()
        );
    }

    /**
     * @dataProvider dataGetContentWorks
     */
    public function testGetContentWorks(?int $expectedSize, ?int $expectedMargin)
    {
        $expectedSPayD = 'Expected SPayD';
        $expectedContent = 'Expected content';

        $spayd = $this->getMockBuilder(Spayd::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $spayd->expects($this->once())
            ->method('generate')
            ->willReturn($expectedSPayD);

        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSize', 'setMargin', 'setText', 'writeString'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('setSize')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCode->expects($this->once())
            ->method('setMargin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
        $qrCode->expects($this->once())
            ->method('setText')
            ->with($expectedSPayD);
        $qrCode->expects($this->once())
            ->method('writeString')
            ->willReturn($expectedContent);

        $this->assertEquals(
            $expectedContent,
            $this->getSpaydQr($spayd, $qrCode)->getContent(...$this->trimArgs([$expectedSize, $expectedMargin]))
        );
    }

    public function dataGetContentWorks()
    {
        return [
            [null, null],
            [123, null],
            [123, 456],
        ];
    }

    /**
     * @dataProvider dataGetDataUriWorks
     */
    public function testGetDataUriWorks(?int $expectedSize, ?int $expectedMargin)
    {
        $expectedSPayD = 'Expected SPayD';
        $expectedDataUri = 'Expected data URI';

        $spayd = $this->getMockBuilder(Spayd::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $spayd->expects($this->once())
            ->method('generate')
            ->willReturn($expectedSPayD);

        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSize', 'setMargin', 'setText', 'writeDataUri'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('setSize')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCode->expects($this->once())
            ->method('setMargin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
        $qrCode->expects($this->once())
            ->method('setText')
            ->with($expectedSPayD);
        $qrCode->expects($this->once())
            ->method('writeDataUri')
            ->willReturn($expectedDataUri);

        $this->assertEquals(
            $expectedDataUri,
            $this->getSpaydQr($spayd, $qrCode)->getDataUri(...$this->trimArgs([$expectedSize, $expectedMargin]))
        );
    }

    public function dataGetDataUriWorks()
    {
        return $this->dataGetContentWorks();
    }

    /**
     * @dataProvider dataWriteFileWorks
     */
    public function testWriteFileWorks(?int $expectedSize, ?int $expectedMargin)
    {
        $expectedSPayD = 'Expected SPayD';
        $expectedPath = 'Expected path';

        $spayd = $this->getMockBuilder(Spayd::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $spayd->expects($this->once())
            ->method('generate')
            ->willReturn($expectedSPayD);

        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSize', 'setMargin', 'setText', 'writeFile'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('setSize')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCode->expects($this->once())
            ->method('setMargin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
        $qrCode->expects($this->once())
            ->method('setText')
            ->with($expectedSPayD);
        $qrCode->expects($this->once())
            ->method('writeFile')
            ->with($expectedPath);

        $this->getSpaydQr($spayd, $qrCode)->writeFile(...$this->trimArgs([$expectedPath, $expectedSize, $expectedMargin]));
    }

    public function dataWriteFileWorks()
    {
        return $this->dataGetContentWorks();
    }

    private function getSpaydQr(?Spayd $spayd, ?QrCode $qrCode): SpaydQr
    {
        return new SpaydQr(
            $spayd ?: new Spayd(),
            $qrCode ?: new QrCode(),
            static::IBAN,
            Money::EUR(100)
        );
    }

    private function trimArgs(array $args): array
    {
        $trimmed = [];
        foreach ($args as $arg) {
            if (null === $arg) {
                return $trimmed;
            }
            $trimmed[] = $arg;
        }
        return $trimmed;
    }
}
