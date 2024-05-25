<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Sunfox\Spayd\Spayd;

class SpaydQrTest extends TestCase
{
    private const IBAN = 'CZ7801000000000000000123';

    public function testFactoryWorks(): void
    {
        $this->assertEquals(
            'SPD*1.0*ACC:CZ7801000000000000000123*AM:799.50*CC:CZK*CRC32:8a0f48b6',
            SpaydQr::create(
                self::IBAN,
                Money::CZK(79950)
            )->spayd->build(),
        );
    }

    public function testSetWriterWorks(): void
    {
        $writer = QrCodeWriter::Svg;
        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('writer')
            ->with($writer->endroid());

        SpaydQr::testable(qrCodeBuilder: $qrCodeBuilder)->setWriter($writer);
    }

    public function testSetVariableSymbolWorks(): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)
            ->getMock();
        $spayd->expects($this->once())
            ->method('add')
            ->with(SpaydKey::VariableSymbol->value, 123);

        SpaydQr::testable(spayd: $spayd)->setVariableSymbol(123);
    }

    /**
     * @see SpaydBuilderTest::testAddsInvoice()
     */
    public function testSetInvoiceWorks(): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)
            ->getMock();
        $spayd->expects($this->once())
            ->method('add')
            ->with(SpaydKey::Invoice->value);

        SpaydQr::testable(spayd: $spayd)->setInvoice(
            'INV123',
            new \DateTimeImmutable('2019-06-03'),
            12345678,
            null,
            null,
            null,
            null,
        );
    }

    public function testGetContentTypeWorks(): void
    {
        $expectedContentType = 'Expected content type';

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('build')
            ->willReturn($qrCodeResult);
        $qrCodeResult->expects($this->once())
            ->method('getMimeType')
            ->willReturn($expectedContentType);

        $this->assertEquals(
            $expectedContentType,
            SpaydQr::testable(qrCodeBuilder: $qrCodeBuilder)->getContentType()
        );
    }

    /** @dataProvider dataGetContentWorks */
    public function testGetContentWorks(?int $expectedSize, ?int $expectedMargin): void
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQrInterface::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQrInterface::QR_MARGIN);
        $qrCodeBuilder->expects($this->once())
            ->method('data')
            ->with($expectedSPayD);
        $qrCodeBuilder->expects($this->once())
            ->method('build')
            ->willReturn($qrCodeResult);
        $qrCodeResult->expects($this->once())
            ->method('getString')
            ->willReturn($expectedContent);

        $this->assertEquals(
            $expectedContent,
            SpaydQr::testable($spayd, $qrCodeBuilder)->getContent(...$this->trimArgs([$expectedSize, $expectedMargin]))
        );
    }

    public function dataGetContentWorks(): array
    {
        return [
            [null, null],
            [123, null],
            [123, 456],
        ];
    }

    /** @dataProvider dataGetDataUriWorks */
    public function testGetDataUriWorks(?int $expectedSize, ?int $expectedMargin): void
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQrInterface::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQrInterface::QR_MARGIN);
        $qrCodeBuilder->expects($this->once())
            ->method('data')
            ->with($expectedSPayD);
        $qrCodeBuilder->expects($this->once())
            ->method('build')
            ->willReturn($qrCodeResult);
        $qrCodeResult->expects($this->once())
            ->method('getDataUri')
            ->willReturn($expectedDataUri);

        $this->assertEquals(
            $expectedDataUri,
            SpaydQr::testable($spayd, $qrCodeBuilder)->getDataUri(...$this->trimArgs([$expectedSize, $expectedMargin]))
        );
    }

    public function dataGetDataUriWorks(): array
    {
        return $this->dataGetContentWorks();
    }

    /** @dataProvider dataWriteFileWorks */
    public function testWriteFileWorks(?int $expectedSize, ?int $expectedMargin): void
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQrInterface::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQrInterface::QR_MARGIN);
        $qrCodeBuilder->expects($this->once())
            ->method('data')
            ->with($expectedSPayD);
        $qrCodeBuilder->expects($this->once())
            ->method('build')
            ->willReturn($qrCodeResult);
        $qrCodeResult->expects($this->once())
            ->method('saveToFile')
            ->with($expectedPath);

        SpaydQr::testable($spayd, $qrCodeBuilder)->writeFile(...$this->trimArgs([$expectedPath, $expectedSize, $expectedMargin]));
    }

    public function dataWriteFileWorks(): array
    {
        return $this->dataGetContentWorks();
    }

    public function testEndToEnd(): void
    {
        $spaydQr = SpaydQr::testable()
            ->setWriter(QrCodeWriter::Svg)
            ->setVariableSymbol(123)
            ->setInvoice(
                '1',
                new \DateTime('2019-06-05'),
                2,
                'CZ2',
                3,
                'CZ3',
                'string',
            );

        $this->assertNotEmpty($spaydQr->spayd->build());
        $this->assertNotEmpty($spaydQr->getDataUri());
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
