<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use DateTimeInterface;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use PetrKnap\Shorts\Exception\MissingRequirement;
use Stringable;
use Sunfox\Spayd\Spayd as InnerSpayd;
use Throwable;

/**
 * Short Payment Descriptor
 *
 * @see https://qr-platba.cz/pro-vyvojare/specifikace-formatu/
 *
 * @phpstan-type TIban = non-empty-string
 * @phpstan-type TBic = non-empty-string
 * @phpstan-type TAccount = array{0: TIban, 1: TBic}|TIban
 */
final class Spayd implements Stringable
{
    public const FILE_EXTENSION = 'spayd';
    public const KEY_VALUE_SEPARATOR = ':';
    public const KEY_VALUE_TERMINATOR = '*';
    public const MIME_TYPE = 'application/x-shortpaymentdescriptor';

    /**
     * @param TAccount $account
     * @param array<string, string> $keyValueMap
     */
    public function __construct(
        private readonly array|string $account,
        private readonly array $keyValueMap = [],
    ) {
    }

    /**
     * @param TAccount $account
     * @param ($invoice is null ? Money : null) $amount
     * @param ($amount is null ? Spayd\Sind : null) $invoice
     */
    public static function create(
        array|string $account,
        ?Money $amount = null,
        ?Spayd\Sind $invoice = null,
    ): self {
        $instance = (new self($account));

        if ($invoice === null) {
            /** @var Money $amount */
            return $instance->withAmount($amount);
        }

        return $instance->withInvoice($invoice);
    }

    /**
     * @note use at your own risk
     */
    public function with(Spayd\Key|string $key, string $value): self
    {
        return new self(
            $this->account,
            [
                ...$this->keyValueMap,
                is_string($key) ? $key : $key->value => $value,
            ],
        );
    }

    /**
     * @param array<TAccount> $alternativeAccounts
     */
    public function withAlternativeAccounts(array $alternativeAccounts): self
    {
        return $this->with(Spayd\Key::AlternativeAccount, self::normalizeAlternativeAccounts($alternativeAccounts));
    }

    public function withAmount(Money $amount): self
    {
        return $this
            ->with(Spayd\Key::Amount, self::normalizeMoneyAmount($amount))
            ->with(Spayd\Key::CurrencyCode, self::normalizeMoneyCurrency($amount))
        ;
    }

    public function withConstantSymbol(int $constantSymbol): self
    {
        return $this->with(Spayd\Key::ConstantSymbol, (string) $constantSymbol);
    }

    public function withDueDate(DateTimeInterface $dueDate): self
    {
        return $this->with(Spayd\Key::DueDate, self::normalizeDate($dueDate));
    }

    /**
     * @note internally calls {@see self::withAmount()}
     */
    public function withInvoice(Spayd\Sind $invoice): self
    {
        return $this->withAmount($invoice->amount)->with(Spayd\Key::Invoice, (string) $invoice);
    }

    /**
     * @param non-empty-string $message
     */
    public function withMessage(string $message): self
    {
        return $this->with(Spayd\Key::Message, $message);
    }

    public function withPaymentType(Spayd\PaymentType $paymentType): self
    {
        return $this->with(Spayd\Key::PaymentType, $paymentType->value);
    }

    public function withRecipientName(string $recipientName): self
    {
        return $this->with(Spayd\Key::RecipientName, $recipientName);
    }

    public function withReference(int $reference): self
    {
        return $this->with(Spayd\Key::Reference, (string) $reference);
    }

    public function withSpecificSymbol(int $specificSymbol): self
    {
        return $this->with(Spayd\Key::SpecificSymbol, (string) $specificSymbol);
    }

    public function withVariableSymbol(int $variableSymbol): self
    {
        return $this->with(Spayd\Key::VariableSymbol, (string) $variableSymbol);
    }

    /**
     * @return string {@see Spayd::MIME_TYPE} value
     *
     * @throws Exception\CouldNotSerializeSpayd
     */
    public function __toString()
    {
        try {
            $innerSpayd = (new InnerSpayd())->add(Spayd\Key::Account->value, self::normalizeAccount($this->account));

            foreach ($this->keyValueMap as $key => $value) {
                $innerSpayd->add($key, $value);
            }

            return $innerSpayd->generate();
        } catch (Throwable $reason) {
            throw new Exception\CouldNotSerializeSpayd($reason);
        }
    }

    /**
     * @param TAccount $value
     */
    private static function normalizeAccount(array|string $value): string
    {
        if (is_array($value)) {
            return implode('+', $value);
        }
        return $value;
    }

    /**
     * @param array<TAccount> $value
     */
    private static function normalizeAlternativeAccounts(array $value): string
    {
        return implode(',', array_map(
            static fn(array|string $v): string => self::normalizeAccount($v),
            $value,
        ));
    }

    private static function normalizeDate(DateTimeInterface $value): string
    {
        return $value->format('Ymd');
    }

    private static function normalizeMoneyAmount(Money $value): string
    {
        return (new DecimalMoneyFormatter(new ISOCurrencies()))->format($value);
    }

    private static function normalizeMoneyCurrency(Money $value): string
    {
        return $value->getCurrency()->getCode();
    }
}
