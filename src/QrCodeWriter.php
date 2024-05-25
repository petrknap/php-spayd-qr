<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Endroid\QrCode\Writer\WriterInterface;

enum QrCodeWriter
{
    case Png;
    case Svg;
    case WebP;

    /**
     * @internal factory
     */
    public function endroid(): WriterInterface
    {
        return match ($this) {
            self::Png => new PngWriter(),
            self::Svg => new SvgWriter(),
            self::WebP => new WebPWriter(),
        };
    }
}
