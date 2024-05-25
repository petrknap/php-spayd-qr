<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

enum SpaydKey: string
{
    case Amount = 'AM';
    case CurrencyCode = 'CC';
    case Iban = 'ACC';
    case Invoice = 'X-INV';
    case VariableSymbol = 'X-VS';
}
