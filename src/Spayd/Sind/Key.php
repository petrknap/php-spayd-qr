<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Spayd\Sind;

enum Key: string
{
    case BuyerIdentificationNumber = 'INR';
    case BuyerVatIdentificationNumber = 'VIR';
    case Id = 'ID';
    case IssueDate = 'DD';
    case Message = 'MSG';
    case SellerIdentificationNumber = 'INI';
    case SellerVatIdentificationNumber = 'VII';
}
