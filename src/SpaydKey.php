<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

/**
 * @see https://qr-platba.cz/pro-vyvojare/specifikace-formatu/
 */
enum SpaydKey: string
{
    case AlternativeIban = 'ALT-ACC';
    case Amount = 'AM';
    case CurrencyCode = 'CC';
    case DueDate = 'DT';
    case Iban = 'ACC';
    case Invoice = 'X-INV'; # https://qr-faktura.cz/
    case Message = 'MSG';
    case NotificationType = 'NT';
    case PaymentType = 'PT';
    case RecipientName = 'RN';
    case Reference = 'RF';
    case VariableSymbol = 'X-VS';
}
