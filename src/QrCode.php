<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\QrCode as InnerQrCode;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;
use Stringable;
use Throwable;

final class QrCode
{
    public const MARGIN = 0;
    public const SIZE = 300;
    public const WRITER = QrCode\Writer::Png;

    private readonly InnerQrCode $innerQrCode;
    private readonly WriterInterface $writer;

    public function __construct(
        Stringable|string $data,
        QrCode\Writer|WriterInterface $writer = self::WRITER,
    ) {
        $innerQrCode = new InnerQrCode((string) $data);
        $this->innerQrCode = $innerQrCode;
        $this->writer = $writer instanceof WriterInterface ? $writer : $writer->create();
    }

    public static function asDataUri(
        Spayd $payment,
        int $size = self::SIZE,
        int $margin = self::MARGIN,
        QrCode\Writer|WriterInterface $writer = self::WRITER,
    ): string {
        return (new self($payment, $writer))->getDataUri($size, $margin);
    }

    public function getContentType(): string
    {
        return $this->generate(self::SIZE, self::MARGIN)->getMimeType();
    }

    public function getContent(int $size = self::SIZE, int $margin = self::MARGIN): string
    {
        return $this->generate($size, $margin)->getString();
    }

    public function getDataUri(int $size = self::SIZE, int $margin = self::MARGIN): string
    {
        return $this->generate($size, $margin)->getDataUri();
    }

    private function generate(int $size, int $margin): ResultInterface
    {
        try {
            $innerQrCode = clone $this->innerQrCode;
            $innerQrCode->setSize($size);
            $innerQrCode->setMargin($margin);

            return $this->writer->write($innerQrCode);
        } catch (Throwable $reason) {
            throw new Exception\CouldNotGenerateQrCode($reason);
        }
    }
}
