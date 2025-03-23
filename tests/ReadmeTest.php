<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use PetrKnap\Shorts\PhpUnit\MarkdownFileTestInterface;
use PetrKnap\Shorts\PhpUnit\MarkdownFileTestTrait;
use PHPUnit\Framework\TestCase;

final class ReadmeTest extends TestCase implements MarkdownFileTestInterface
{
    use MarkdownFileTestTrait;

    public static function getPathToMarkdownFile(): string
    {
        return __DIR__ . '/../README.md';
    }

    public static function getExpectedOutputsOfPhpExamples(): iterable
    {
        return [
            'usage-template' => file_get_contents(__DIR__ . '/ReadmeTest/example.html'),
            'usage-variable-symbol' => '',
            'usage-invoice' => '',
        ];
    }
}
