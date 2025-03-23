<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\QrCode;

use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Endroid\QrCode\Writer\WriterInterface;

enum Writer
{
    case Png;
    case Svg;
    case WebP;

    /**
     * @internal factory
     */
    public function create(): WriterInterface
    {
        return match ($this) {
            self::Png => new PngWriter(),
            self::Svg => new SvgWriter(),
            self::WebP => new WebPWriter(),
        };
    }
}
