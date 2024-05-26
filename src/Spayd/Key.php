<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Spayd;

enum Key: string
{
    case Account = 'ACC';
    case AlternativeAccount = 'ALT-ACC';
    case Amount = 'AM';
    case Checksum = 'CRC32';
    case ConstantSymbol = 'X-KS';
    case CurrencyCode = 'CC';
    case DueDate = 'DT';
    case Invoice = 'X-INV';
    case Message = 'MSG';
    case NotificationType = 'NT';
    case PaymentType = 'PT';
    case RecipientName = 'RN';
    case Reference = 'RF';
    case SpecificSymbol = 'X-SS';
    case VariableSymbol = 'X-VS';
}
