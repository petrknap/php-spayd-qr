<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Sunfox\Spayd\Spayd;

class SpaydQrTest extends TestCase
{
    private const IBAN = 'CZ7801000000000000000123';

    public function testFactoryWorks()
    {
        $spaydQr = SpaydQr::create(
            self::IBAN,
            Money::CZK(79950)
        );

        $this->assertEquals(
            'SPD*1.0*ACC:CZ7801000000000000000123*AM:799.50*CC:CZK*CRC32:8a0f48b6',
            $this->getPrivateProperty($spaydQr, 'spayd')->generate()
        );
    }

    public function testSetWriterWorks()
    {
        $writer = QrCodeWriter::Svg;
        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('writer')
            ->with($writer->endroid())
            ->willReturnSelf();

        $this->getSpaydQr(null, $qrCodeBuilder)->setWriter($writer);
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
            $this->getSpaydQr(null, $qrCodeBuilder)->getContentType()
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
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
            $this->getSpaydQr($spayd, $qrCodeBuilder)->getContent(...$this->trimArgs([$expectedSize, $expectedMargin]))
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
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
            $this->getSpaydQr($spayd, $qrCodeBuilder)->getDataUri(...$this->trimArgs([$expectedSize, $expectedMargin]))
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

        $qrCodeBuilder = $this->getMockBuilder(BuilderInterface::class)->getMock();
        $qrCodeResult = $this->getMockBuilder(ResultInterface::class)->getMock();
        $qrCodeBuilder->expects($this->once())
            ->method('size')
            ->with($expectedSize ?: SpaydQr::QR_SIZE);
        $qrCodeBuilder->expects($this->once())
            ->method('margin')
            ->with($expectedMargin ?: SpaydQr::QR_MARGIN);
        $qrCodeBuilder->expects($this->once())
            ->method('data')
            ->with($expectedSPayD);
        $qrCodeBuilder->expects($this->once())
            ->method('build')
            ->willReturn($qrCodeResult);
        $qrCodeResult->expects($this->once())
            ->method('saveToFile')
            ->with($expectedPath);

        $this->getSpaydQr($spayd, $qrCodeBuilder)->writeFile(...$this->trimArgs([$expectedPath, $expectedSize, $expectedMargin]));
    }

    public function dataWriteFileWorks()
    {
        return $this->dataGetContentWorks();
    }

    public function testEndToEnd()
    {
        $spaydQr = $this->getSpaydQr(null, null)
            ->setWriter(QrCodeWriter::Svg)
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
        $this->assertNotEmpty($spaydQr->getQrCodeBuilder()->build()->getDataUri());
    }

    private function getSpaydQr(?Spayd $spayd, ?BuilderInterface $qrCodeBuilder)
    {
        return new class (
            $spayd ?: new Spayd(),
            $qrCodeBuilder ?: Builder::create(),
            self::IBAN,
            Money::EUR(100)
        ) extends SpaydQr {
            public function __construct(...$args)
            {
                parent::__construct(...$args);
            }

            public function getSpayd(): Spayd
            {
                return $this->spayd;
            }

            public function getQrCodeBuilder(): BuilderInterface
            {
                $this->buildQrCode(null, null);

                return $this->qrCodeBuilder;
            }
        };
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

    private function getPrivateProperty(object $classInstance, string $propertyName): mixed
    {
        $object = new \ReflectionObject($classInstance);
        $property = $object->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($classInstance);
    }
}
