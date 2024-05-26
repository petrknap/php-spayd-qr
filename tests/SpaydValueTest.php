<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Stringable;

class SpaydValueTest extends TestCase
{
    /**
     * @dataProvider dataNormalizesByKey
     * @depends testNormalizes
     */
    public function testNormalizesByKey(SpaydKey|null $key, mixed $value, string $expected): void
    {
        self::assertSame(
            $expected,
            SpaydValue::normalize($key, $value),
        );
    }

    public static function dataNormalizesByKey(): iterable
    {
        foreach (
            [
                [null, 'test', 'test'],
            ] as $data
        ) {
            yield $data[0]?->value ?? 'null' => $data;
        }
    }

    /**
     * @dataProvider dataNormalizes
     */
    public function testNormalizes(string $what, mixed $value, string $expected, bool $shouldThrow)
    {
        if ($shouldThrow) {
            self::expectException(InvalidArgumentException::class);
        }

        self::assertSame(
            $expected,
            call_user_func(SpaydValue::class . '::normalize' . ucfirst($what), $value),
        );
    }

    /**
     * @dataProvider dataNormalizes
     */
    public static function dataNormalizes(): iterable
    {
        $stringable = new class () implements Stringable {
            public function __toString()
            {
                return 'stringable';
            }
        };
        foreach (
            [
                ['string', 'string', 'string', false],
                ['string', $stringable, 'stringable', false],
                ['string', null, 'null', true],
            ] as $index => $data
        ) {
            yield sprintf(
                '%s($i%d) = %s',
                $data[0],
                $index,
                $data[3] ? InvalidArgumentException::class : $data[2],
            ) => $data;
        }
    }
}
