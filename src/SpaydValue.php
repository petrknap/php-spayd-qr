<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Assert\Assert;
use InvalidArgumentException;
use Stringable;
use Throwable;

/**
 * Each normalizer must be lossless, like {@see SpaydValue::normalize()}
 */
final class SpaydValue
{
    /**
     * @throws Throwable if value is not losslessly normalizable
     */
    public static function normalize(?SpaydKey $key, mixed $value): string
    {
        return match ($key) {
            default => self::normalizeString($value),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function normalizeString(mixed $value): string
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }
        Assert::that($value)->string();
        /** @var string */
        return $value;
    }
}
