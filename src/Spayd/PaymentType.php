<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Spayd;

enum PaymentType: string
{
    case InstantPayment = 'IP';
}
