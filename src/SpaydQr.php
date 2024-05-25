<?php

declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Money\Money;
use Sunfox\Spayd\Spayd;

final class SpaydQr implements SpaydQrInterface
{
    private function __construct(
        public readonly SpaydBuilder $spayd,
        private readonly BuilderInterface $qrCodeBuilder,
    ) {
    }

    public static function create(string $iban, Money $money, QrCodeWriter $writer = QrCodeWriter::Png): self
    {
        return new self(
            SpaydBuilder::create()
                ->add(SpaydKey::Iban, $iban)
                ->add(SpaydKey::Amount, SpaydBuilder::getAmount($money))
                ->add(SpaydKey::CurrencyCode, SpaydBuilder::getCurrencyCode($money)),
            Builder::create()
                ->writer($writer->endroid())
                ->encoding(new Encoding('UTF-8')),
        );
    }

    /**
     * @internal for testing purposes only
     */
    public static function testable(?Spayd $spayd = null, ?BuilderInterface $qrCodeBuilder = null): self
    {
        return new self(
            SpaydBuilder::testable($spayd),
            $qrCodeBuilder ?? Builder::create(),
        );
    }

    public function setVariableSymbol(int $variableSymbol): self
    {
        $this->spayd
            ->remove(SpaydKey::VariableSymbol)
            ->add(SpaydKey::VariableSymbol, (string) $variableSymbol);
        return $this;
    }

    public function setInvoice(
        string $id,
        \DateTimeInterface $issueDate,
        int $sellerIdentificationNumber,
        ?string $sellerVatIdentificationNumber,
        ?int $buyerIdentificationNumber,
        ?string $buyerVatIdentificationNumber,
        ?string $description,
    ): self {
        $this->spayd
            ->remove(SpaydKey::Invoice)
            ->addInvoice(
                $id,
                $issueDate,
                $sellerIdentificationNumber,
                $sellerVatIdentificationNumber,
                $buyerIdentificationNumber,
                $buyerVatIdentificationNumber,
                $description,
            );
        return $this;
    }

    public function setWriter(QrCodeWriter $writer): self
    {
        $this->qrCodeBuilder
            ->writer($writer->endroid());
        return $this;
    }

    public function getContentType(): string
    {
        return $this->buildQrCode(null, null)->getMimeType();
    }

    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->buildQrCode($size, $margin)->getString();
    }

    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->buildQrCode($size, $margin)->getDataUri();
    }

    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void
    {
        $this->buildQrCode($size, $margin)->saveToFile($path);
    }

    private function buildQrCode(?int $size, ?int $margin): ResultInterface
    {
        $this->qrCodeBuilder->data($this->spayd->build());

        if ($size !== null) {
            $this->qrCodeBuilder->size($size);
        }

        if ($margin !== null) {
            $this->qrCodeBuilder->margin($margin);
        }

        return $this->qrCodeBuilder->build();
    }
}
