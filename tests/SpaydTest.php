<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use DateTimeImmutable;
use Money\Money;
use PetrKnap\SpaydQr\Spayd\PaymentType;
use PHPUnit\Framework\TestCase;

final class SpaydTest extends TestCase
{
    /**
     * @dataProvider dataIsStringable
     */
    public function testIsStringable(Spayd $spayd, array $expected): void
    {
        $actual = explode(Spayd::KEY_VALUE_TERMINATOR, (string) $spayd);
        sort($actual);
        sort($expected);
        self::assertEquals($expected, $actual);
    }

    public static function dataIsStringable(): iterable
    {
        $spayd = new Spayd(
            account: ['CZ6508000000192000145399', 'CNBACZPP'],
        );
        $expected = [
            'SPD',
            '1.0',
            Spayd\Key::Account->value . Spayd::KEY_VALUE_SEPARATOR . 'CZ6508000000192000145399+CNBACZPP',
        ];
        yield 'base' => [$spayd, [
            Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '555167b9',
            ...$expected,
        ]];

        yield Spayd\Key::AlternativeAccount->name => [
            $spayd->withAlternativeAccounts([
                ['CZ6907101781240000004159', 'GIBACZPX'],
            ]),
            [
                Spayd\Key::AlternativeAccount->value . Spayd::KEY_VALUE_SEPARATOR . 'CZ6907101781240000004159+GIBACZPX',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . 'daa2999e',
                ...$expected,
            ],
        ];

        yield Spayd\Key::Amount->name => [
            $spayd->withAmount(Money::CZK(100)),
            [
                Spayd\Key::Amount->value . Spayd::KEY_VALUE_SEPARATOR . '1.00',
                Spayd\Key::CurrencyCode->value . Spayd::KEY_VALUE_SEPARATOR . 'CZK',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '2f58d75f',
                ...$expected,
            ],
        ];

        yield Spayd\Key::ConstantSymbol->name => [
            $spayd->withConstantSymbol(1),
            [
                Spayd\Key::ConstantSymbol->value . Spayd::KEY_VALUE_SEPARATOR . '1',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '2009656f',
                ...$expected,
            ],
        ];

        yield Spayd\Key::DueDate->name => [
            $spayd->withDueDate(new DateTimeImmutable('2025-03-16')),
            [
                Spayd\Key::DueDate->value . Spayd::KEY_VALUE_SEPARATOR . '20250316',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '8a00ab25',
                ...$expected,
            ],
        ];

        $sind = new Spayd\Sind(
            'INV#123',
            new DateTimeImmutable('2025-03-16'),
            Money::CZK(123),
        );
        yield Spayd\Key::Invoice->name => [
            $spayd->withInvoice($sind),
            [
                Spayd\Key::Amount->value . Spayd::KEY_VALUE_SEPARATOR . '1.23',
                Spayd\Key::CurrencyCode->value . Spayd::KEY_VALUE_SEPARATOR . 'CZK',
                Spayd\Key::Invoice->value . Spayd::KEY_VALUE_SEPARATOR . (string) $sind,
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '14519da5',
                ...$expected,
            ],
        ];

        yield Spayd\Key::Message->name => [
            $spayd->withMessage('TEST'),
            [
                Spayd\Key::Message->value . Spayd::KEY_VALUE_SEPARATOR . 'TEST',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '80194af1',
                ...$expected,
            ],
        ];

        yield Spayd\Key::PaymentType->name => [
            $spayd->withPaymentType(Spayd\PaymentType::InstantPayment),
            [
                Spayd\Key::PaymentType->value . Spayd::KEY_VALUE_SEPARATOR . Spayd\PaymentType::InstantPayment->value,
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '7b4784f',
                ...$expected,
            ],
        ];

        yield Spayd\Key::RecipientName->name => [
            $spayd->withRecipientName('TEST'),
            [
                Spayd\Key::RecipientName->value . Spayd::KEY_VALUE_SEPARATOR . 'TEST',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '183afaf8',
                ...$expected,
            ],
        ];

        yield Spayd\Key::Reference->name => [
            $spayd->withReference(1),
            [
                Spayd\Key::Reference->value . Spayd::KEY_VALUE_SEPARATOR . '1',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '3da360e9',
                ...$expected,
            ],
        ];

        yield Spayd\Key::SpecificSymbol->name => [
            $spayd->withSpecificSymbol(1),
            [
                Spayd\Key::SpecificSymbol->value . Spayd::KEY_VALUE_SEPARATOR . '1',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . 'b5a41a1f',
                ...$expected,
            ],
        ];

        yield Spayd\Key::VariableSymbol->name => [
            $spayd->withVariableSymbol(1),
            [
                Spayd\Key::VariableSymbol->value . Spayd::KEY_VALUE_SEPARATOR . '1',
                Spayd\Key::Checksum->value . Spayd::KEY_VALUE_SEPARATOR . '827aea2d',
                ...$expected,
            ],
        ];
    }
}
