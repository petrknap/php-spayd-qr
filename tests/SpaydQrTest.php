<?php declare(strict_types=1);

namespace PetrKnap\SpaydQr\Test;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Money\Money;
use PetrKnap\SpaydQr\SpaydQr;
use PHPUnit\Framework\TestCase;
use Shoptet\Spayd\Spayd;

class SpaydQrTest extends TestCase
{
    private const IBAN = 'CZ7801000000000000000123';

    public function testFactoryWorks()
    {
        $spaydQr = SpaydQr::create(
            self::IBAN,
            Money::CZK(79950)
        );

        $this->assertInstanceOf(SpaydQr::class, $spaydQr);
        $this->assertEquals(
            'SPD*1.0*ACC:CZ7801000000000000000123*AM:799.50*CC:CZK*CRC32:8a0f48b6',
            $spaydQr->getSpayd()->generate()
        );
    }

    public function testSetWriterWorks()
    {
        $writer = $this->getMockBuilder(WriterInterface::class)->getMock();
        $qrCode = $this->getMockBuilder(QrCode::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWriter'])
            ->getMock();
        $qrCode->expects($this->once())
            ->method('setWriter')
            ->with($writer)
            ->willReturnSelf();

        $this->getSpaydQr(null, $qrCode)->setWriter($writer);
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

    /** @dataProvider dataSetInvoiceWorks */
    public function testSetInvoiceWorks(?string $stin, ?int $bin, ?string $btin, ?string $description, string $expected)
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
            ->with(SpaydQr::SPAYD_INVOICE, $expected)
            ->willReturnSelf();

        $this->getSpaydQr($spayd, null)->setInvoice(
            'INV123',
            new \DateTimeImmutable('2019-06-03'),
            12345678,
            $stin,
            $bin,
            $btin,
            $description
        );
    }

    public function dataSetInvoiceWorks()
    {
        return [
            ['CZ12345678', 23456789, 'CZ23456789', 'See *https://qr-faktura.cz/*', 'SID%2A1.0%2AID:INV123%2ADD:20190603%2AINI:12345678%2AVII:CZ12345678%2AINR:23456789%2AVIR:CZ23456789%2AMSG:See https://qr-faktura.cz/'],
            [null, null, null, null, 'SID%2A1.0%2AID:INV123%2ADD:20190603%2AINI:12345678'],
        ];
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

    /** @dataProvider dataGetContentWorks */
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

    /** @dataProvider dataGetDataUriWorks */
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

    /** @dataProvider dataWriteFileWorks */
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

    public function testEndToEnd()
    {
        $spaydQr = $this->getSpaydQr(new Spayd(), new QrCode())
            ->setWriter(new SvgWriter())
            ->setVariableSymbol(123)
            ->setInvoice(
                '1',
                new \DateTime('2019-06-05'),
                2,
                'CZ2',
                3,
                'CZ3',
                'string'
            );

        $this->assertNotEmpty($spaydQr->getSpayd()->generate());
        $this->assertNotEmpty($spaydQr->getQrCode()->getData());
    }

    private function getSpaydQr(?Spayd $spayd, ?QrCode $qrCode): SpaydQr
    {
        return new SpaydQr(
            $spayd ?: new Spayd(),
            $qrCode ?: new QrCode(),
            self::IBAN,
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
