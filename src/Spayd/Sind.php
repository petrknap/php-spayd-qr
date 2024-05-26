<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Spayd;

use DateTimeInterface;
use Money\Money;
use PetrKnap\SpaydQr\Spayd;
use Stringable;

/**
 * Short Invoice Descriptor
 *
 * @see https://qr-faktura.cz/popis-formatu
 */
final class Sind implements Stringable
{
    public const KEY_VALUE_SEPARATOR = ':';
    public const KEY_VALUE_TERMINATOR = '%2A';
    private const FORMAT = 'SID';
    private const VERSION = '1.0';

    /**
     * @param array<string, string> $keyValueMap
     */
    public function __construct(
        private readonly string $id,
        private readonly DateTimeInterface $issueDate,
        public readonly Money $amount,
        private readonly array $keyValueMap = [],
    ) {
    }

    public static function create(
        string $id,
        DateTimeInterface $issueDate,
        Money $amount,
    ): self {
        return new self($id, $issueDate, $amount);
    }

    /**
     * @note use at your own risk
     */
    public function with(Sind\Key|string $key, string $value): self
    {
        return new self(
            $this->id,
            $this->issueDate,
            $this->amount,
            [
                ...$this->keyValueMap,
                is_string($key) ? $key : $key->value => $value,
            ],
        );
    }

    public function withBuyerIdentificationNumber(int $buyerIdentificationNumber): self
    {
        return $this->with(Sind\Key::BuyerIdentificationNumber, (string) $buyerIdentificationNumber);
    }

    public function withBuyerVatIdentificationNumber(string $buyerVatIdentificationNumber): self
    {
        return $this->with(Sind\Key::BuyerVatIdentificationNumber, self::normalizeString($buyerVatIdentificationNumber));
    }

    public function withMessage(string $message): self
    {
        return $this->with(Sind\Key::Message, self::normalizeString($message));
    }

    public function withSellerIdentificationNumber(int $sellerIdentificationNumber): self
    {
        return $this->with(Sind\Key::SellerIdentificationNumber, (string) $sellerIdentificationNumber);
    }

    public function withSellerVatIdentificationNumber(string $sellerVatIdentificationNumber): self
    {
        return $this->with(Sind\Key::SellerVatIdentificationNumber, self::normalizeString($sellerVatIdentificationNumber));
    }

    /**
     * @return string {@see Spayd} value
     */
    public function __toString()
    {
        $invoice = [
            self::FORMAT,
            self::VERSION,
            Sind\Key::Id->value . self::KEY_VALUE_SEPARATOR . self::normalizeString($this->id),
            Sind\Key::IssueDate->value . self::KEY_VALUE_SEPARATOR . self::normalizeDate($this->issueDate),
        ];

        foreach ($this->keyValueMap as $key => $value) {
            $invoice[] = $key . self::KEY_VALUE_SEPARATOR . $value;
        }

        return implode(self::KEY_VALUE_TERMINATOR, $invoice);
    }

    private static function normalizeDate(DateTimeInterface $value): string
    {
        return $value->format('Ymd');
    }

    private static function normalizeString(string $value): string
    {
        return str_ireplace(
            [
                self::KEY_VALUE_TERMINATOR,
                Spayd::KEY_VALUE_TERMINATOR,
            ],
            '',
            $value,
        );
    }
}
