<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr\Spayd;

use DateTimeImmutable;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class SindTest extends TestCase
{
    /**
     * @dataProvider dataIsStringable
     */
    public function testIsStringable(Sind $sind, array $expected): void
    {
        $actual = explode(Sind::KEY_VALUE_TERMINATOR, (string) $sind);
        sort($actual);
        sort($expected);
        self::assertEquals($expected, $actual);
    }

    public static function dataIsStringable(): iterable
    {
        $sind = new Sind(
            id: 'INV#123',
            issueDate: new DateTimeImmutable('2025-03-16'),
            amount: Money::CZK(123),
        );
        $expected = [
            'SID',
            '1.0',
            Sind\Key::Id->value . Sind::KEY_VALUE_SEPARATOR . 'INV#123',
            Sind\Key::IssueDate->value . Sind::KEY_VALUE_SEPARATOR . '20250316',
        ];
        yield 'base' => [$sind, $expected];

        yield Sind\Key::BuyerIdentificationNumber->name => [
            $sind->withBuyerIdentificationNumber(1),
            [
                Sind\Key::BuyerIdentificationNumber->value . Sind::KEY_VALUE_SEPARATOR . '1',
                ...$expected,
            ],
        ];

        yield Sind\Key::BuyerVatIdentificationNumber->name => [
            $sind->withBuyerVatIdentificationNumber('CZ1'),
            [
                Sind\Key::BuyerVatIdentificationNumber->value . Sind::KEY_VALUE_SEPARATOR . 'CZ1',
                ...$expected,
            ],
        ];

        yield Sind\Key::Message->name => [
            $sind->withMessage('MSG'),
            [
                Sind\Key::Message->value . Sind::KEY_VALUE_SEPARATOR . 'MSG',
                ...$expected,
            ],
        ];

        yield Sind\Key::SellerIdentificationNumber->name => [
            $sind->withSellerIdentificationNumber(1),
            [
                Sind\Key::SellerIdentificationNumber->value . Sind::KEY_VALUE_SEPARATOR . '1',
                ...$expected,
            ],
        ];

        yield Sind\Key::SellerVatIdentificationNumber->name => [
            $sind->withSellerVatIdentificationNumber('CZ1'),
            [
                Sind\Key::SellerVatIdentificationNumber->value . Sind::KEY_VALUE_SEPARATOR . 'CZ1',
                ...$expected,
            ],
        ];
    }
}
