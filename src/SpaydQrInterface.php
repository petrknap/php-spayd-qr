<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Writer\WriterInterface;
use Money\Money;
use Sunfox\Spayd\Spayd;

interface SpaydQrInterface
{
    // Specification: https://qr-platba.cz/pro-vyvojare/specifikace-formatu/
    // Field constants
    public const SPAYD_IBAN = 'ACC';
    public const SPAYD_ALT_ACCOUNT = 'ALT-ACC';
    public const SPAYD_AMOUNT = 'AM';
    public const SPAYD_CURRENCY = 'CC';
    public const SPAYD_REFERENCE = 'RF';
    public const SPAYD_RECIPIENT_NAME = 'RN';
    public const SPAYD_DUE_DATE = 'DT';
    public const SPAYD_PAYMENT_TYPE = 'PT';
    public const SPAYD_MESSAGE = 'MSG';
    public const SPAYD_NOTIFICATION_TYPE = 'NT';
    public const SPAYD_NOTIFICATION = 'NO';

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

    // Specification: https://qr-platba.cz/pro-vyvojare/specifikace-formatu/
    // Setters
    /**
     * Identification of the counterparty, which is composed of two components separated by a + sign.
     *
     * @param string $iban
     * @return self
     */
    public function setIban(string $iban): self;

    /**
     * A list of alternate accounts to the default account given by the ACC value. Individual records have the same format as the ACC field and are separated by a comma.
     * Max 2.
     *
     * @param array<int, string> $altAccounts
     * @return self
     */
    public function setAltAccount(array $altAccounts): self;

    // Amount and currency set via \Money\Money $money in constructor

    /**
     * Payment identifier for the payee.
     *
     * @param string $reference
     * @return self
     */
    public function setReference(string $reference): self;

    /**
     * Name of the recipient.
     *
     * @param string $recipientName
     * @return self
     */
    public function setRecipientName(string $recipientName): self;

    /**
     * Due date.
     *
     * @param string $dueDate
     * @return self
     */
    public function setDueDate(string $dueDate): self;

    /**
     * Type of payment.
     *
     * @param string $paymentType
     * @return self
     */
    public function setPaymentType(string $paymentType): self;

    /**
     * Message for the recipient.
     *
     * @param string $message
     * @return self
     */
    public function setMessage(string $message): self;

    /**
     * Identification of the channel for sending the notification to the issuer of the payment.
     *
     * @param string $notificationType
     * @return self
     */
    public function setNotificationType(string $notificationType): self;

    /**
     * Telephone number in international or local terms, or email address.
     *
     * @param string $notification
     * @return self
     */
    public function setNotification(string $notification): self;

    /**
     * Variable symbol. Czech specific reference number.
     *
     * @param int $variableSymbol
     * @return self
     */
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

    public function setWriter(WriterInterface $writer): self;

    public function getContentType(): string;

    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string;

    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void;

    /**
     * Get the underlying Spayd instance if needed.
     *
     * @return Spayd
     */
    public function getSpayd(): Spayd;

    /**
     * Get the underlying QR code builder instance if needed to customize properties like labels, colors, etc.
     *
     * @return BuilderInterface
     */
    public function getQrCodeBuilder(): BuilderInterface;
}
