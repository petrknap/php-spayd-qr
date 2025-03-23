<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class QrCodeTest extends TestCase
{
    private const DATA = 'data';
    private QrCode $qrCode;
    private ResultInterface&MockObject $writeResult;

    public function setUp(): void
    {
        parent::setUp();

        $writer = self::createMock(WriterInterface::class);
        $this->writeResult = self::createMock(ResultInterface::class);
        $this->qrCode = new QrCode(self::DATA, $writer);

        $writer
            ->expects(self::once())
            ->method('write')
            ->willReturn($this->writeResult)
        ;
    }

    public function testGetContentTypeCallsWriter(): void
    {
        $this->writeResult
            ->expects(self::once())
            ->method('getMimeType')
        ;

        $this->qrCode->getContentType();
    }

    public function testGetContentCallsWriter(): void
    {
        $this->writeResult
            ->expects(self::once())
            ->method('getString')
        ;

        $this->qrCode->getContent();
    }

    public function testGetDataUriCallsWriter(): void
    {
        $this->writeResult
            ->expects(self::once())
            ->method('getDataUri')
        ;

        $this->qrCode->getDataUri();
    }
}
