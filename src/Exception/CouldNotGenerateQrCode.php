<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Exception;

use PetrKnap\Shorts\ExceptionWrapper;
use RuntimeException;

final class CouldNotGenerateQrCode extends RuntimeException implements QrCodeException
{
    use ExceptionWrapper;
}
