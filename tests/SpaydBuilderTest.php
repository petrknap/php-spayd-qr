<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use PHPUnit\Framework\TestCase;
use Sunfox\Spayd\Spayd;

class SpaydBuilderTest extends TestCase
{
    public function testBuilds(): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)->getMock();
        $spayd->expects($this->once())
            ->method('generate')
            ->willReturn('test');

        self::assertSame(
            'test',
            SpaydBuilder::testable($spayd)->build(),
        );
    }

    public function testRemoves(): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)->getMock();
        $spayd->expects($this->once())
            ->method('delete')
            ->with(SpaydKey::Amount->value);

        SpaydBuilder::testable($spayd)->remove(SpaydKey::Amount);
    }

    public function testAdds(): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)->getMock();
        $spayd->expects($this->once())
            ->method('add')
            ->with(SpaydKey::Amount->value, 'test');

        SpaydBuilder::testable($spayd)->add(SpaydKey::Amount, 'test');
    }


    /**
     * @dataProvider dataAddsInvoice
     */
    public function testAddsInvoice(?string $stin, ?int $bin, ?string $btin, ?string $description, string $expected): void
    {
        $spayd = $this->getMockBuilder(Spayd::class)
            ->getMock();
        $spayd->expects($this->once())
            ->method('add')
            ->with(SpaydKey::Invoice->value, $expected);

        SpaydBuilder::testable($spayd)->addInvoice(
            'INV123',
            new \DateTimeImmutable('2019-06-03'),
            12345678,
            $stin,
            $bin,
            $btin,
            $description
        );
    }

    public function dataAddsInvoice(): array
    {
        return [
            ['CZ12345678', 23456789, 'CZ23456789', 'See *https://qr-faktura.cz/*', 'SID%2A1.0%2AID:INV123%2ADD:20190603%2AINI:12345678%2AVII:CZ12345678%2AINR:23456789%2AVIR:CZ23456789%2AMSG:See https://qr-faktura.cz/'],
            [null, null, null, null, 'SID%2A1.0%2AID:INV123%2ADD:20190603%2AINI:12345678'],
        ];
    }
}
