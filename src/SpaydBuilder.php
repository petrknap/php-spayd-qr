<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Sunfox\Spayd\Spayd;
use Throwable;

final class SpaydBuilder
{
    # region https://qr-faktura.cz/
    private const INVOICE_BUYER_IDENTIFICATION_NUMBER = 'INR';
    private const INVOICE_BUYER_VAT_IDENTIFICATION_NUMBER = 'VIR';
    private const INVOICE_FORMAT = 'SID';
    private const INVOICE_ID = 'ID';
    private const INVOICE_ISSUE_DATE = 'DD';
    private const INVOICE_MESSAGE = 'MSG';
    private const INVOICE_SELLER_IDENTIFICATION_NUMBER = 'INI';
    private const INVOICE_SELLER_VAT_IDENTIFICATION_NUMBER = 'VII';
    private const INVOICE_VERSION = '1.0';
    # endregion

    private function __construct(
        private readonly Spayd $spayd,
    ) {
    }

    public static function create(): self
    {
        return new self(new Spayd());
    }

    /**
     * @internal for testing purposes only
     */
    public static function testable(?Spayd $spayd = null): self
    {
        return new self($spayd ?? new Spayd());
    }

    public function build(): string
    {
        return $this->spayd->generate();
    }

    public function remove(SpaydKey|string $key): self
    {
        $this->spayd->delete(is_string($key) ? $key : $key->value);

        return $this;
    }

    /**
     * @throws Exception\CouldNotAddKeyWithValue
     */
    public function add(SpaydKey|string $key, mixed $value): self
    {
        try {
            if (is_string($key)) {
                $this->spayd->add($key, SpaydValue::normalize(null, $value));
            } else {
                $this->spayd->add($key->value, SpaydValue::normalize($key, $value));
            }
        } catch (Throwable $reason) {
            throw new Exception\CouldNotAddKeyWithValue($reason);
        }

        return $this;
    }

    public function addInvoice(
        string $id,
        \DateTimeInterface $issueDate,
        int $sellerIdentificationNumber,
        ?string $sellerVatIdentificationNumber,
        ?int $buyerIdentificationNumber,
        ?string $buyerVatIdentificationNumber,
        ?string $description,
    ): self {
        $normalize = static fn (string $input): string => str_replace(
            ['*', '%2A', '%2a'],
            ['', '', ''],
            $input
        );

        $invoice = [
            self::INVOICE_FORMAT, self::INVOICE_VERSION,
            self::INVOICE_ID . ':' . $normalize($id),
            self::INVOICE_ISSUE_DATE . ':' . $issueDate->format('Ymd'),
            self::INVOICE_SELLER_IDENTIFICATION_NUMBER . ':' . $sellerIdentificationNumber,
            $sellerVatIdentificationNumber ? self::INVOICE_SELLER_VAT_IDENTIFICATION_NUMBER . ':' . $normalize($sellerVatIdentificationNumber) : null,
            $buyerIdentificationNumber ? self::INVOICE_BUYER_IDENTIFICATION_NUMBER . ':' . $buyerIdentificationNumber : null,
            $buyerVatIdentificationNumber ? self::INVOICE_BUYER_VAT_IDENTIFICATION_NUMBER . ':' . $normalize($buyerVatIdentificationNumber) : null,
            $description ? self::INVOICE_MESSAGE . ':' . $normalize($description) : null,
        ];

        return $this->add(SpaydKey::Invoice, implode('%2A', array_filter($invoice)));
    }

    public static function getAmount(Money $money): string
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($money);
    }

    public static function getCurrencyCode(Money $money): string
    {
        return $money->getCurrency()->getCode();
    }
}
