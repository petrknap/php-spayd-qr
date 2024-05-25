<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Money\Money;

interface SpaydQrInterface
{
    public const SPAYD_IBAN = 'ACC';
    public const SPAYD_AMOUNT = 'AM';
    public const SPAYD_CURRENCY = 'CC';
    public const SPAYD_VARIABLE_SYMBOL = 'X-VS';
    public const SPAYD_INVOICE = 'X-INV';
    public const SPAYD_INVOICE_FORMAT = 'SID';
    public const SPAYD_INVOICE_VERSION = '1.0';
    public const SPAYD_INVOICE_ID = 'ID';
    public const SPAYD_INVOICE_ISSUE_DATE = 'DD';
    public const SPAYD_INVOICE_SELLER_IDENTIFICATION_NUMBER = 'INI';
    public const SPAYD_INVOICE_SELLER_VAT_IDENTIFICATION_NUMBER = 'VII';
    public const SPAYD_INVOICE_BUYER_IDENTIFICATION_NUMBER = 'INR';
    public const SPAYD_INVOICE_BUYER_VAT_IDENTIFICATION_NUMBER = 'VIR';
    public const SPAYD_INVOICE_MESSAGE = 'MSG';

    public const QR_SIZE = 300;
    public const QR_MARGIN = 0;

    public static function create(string $iban, Money $money): self;

    public function setVariableSymbol(int $variableSymbol): self;

    /**
     * @see https://qr-faktura.cz/
     */
    public function setInvoice(
        string $id,
        \DateTimeInterface $issueDate,
        int $sellerIdentificationNumber,
        ?string $sellerVatIdentificationNumber,
        ?int $buyerIdentificationNumber,
        ?string $buyerVatIdentificationNumber,
        ?string $description
    ): self;

    public function setWriter(QrCodeWriter $writer): self;

    public function getContentType(): string;

    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void;
}
