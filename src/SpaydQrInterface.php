<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Money\Money;

interface SpaydQrInterface
{
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

    /**
     * @param int $size px
     * @param int $margin px
     */
    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    /**
     * @param int $size px
     * @param int $margin px
     */
    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    /**
     * @param int $size px
     * @param int $margin px
     */
    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void;
}
