<?php

namespace PetrKnap\Php\SpaydQr;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Shoptet\Spayd\Spayd;

class SpaydQr
{
    const IBAN = 'ACC';
    const AMOUNT = 'AM';
    const CURRENCY = 'CC';
    const VARIABLE_SYMBOL = 'X-VS';

    private $spayd;

    private $qrCode;

    public function __construct(
        Spayd $spayd,
        QrCode $qrCode,
        string $iban,
        float $amount,
        string $currency
    ) {
        $this->spayd = $spayd
            ->add(static::IBAN, $iban)
            ->add(static::AMOUNT, sprintf('%.2f', $amount))
            ->add(static::CURRENCY, $currency);

        $this->qrCode = $qrCode;
    }

    public static function create(string $iban, float $amount, string $currency): self
    {
        $qrCode = new QrCode();
        $qrCode->setWriter(new PngWriter());

        return new self(
            new Spayd(),
            $qrCode,
            $iban,
            $amount,
            $currency
        );
    }

    public function getSpayd(): Spayd
    {
        return $this->spayd;
    }

    public function getQrCode(): QrCode
    {
        $this->qrCode->setText($this->spayd->generate());

        return $this->qrCode;
    }

    public function setVariableSymbol(int $variableSymbol): self
    {
        $this->spayd->add(static::VARIABLE_SYMBOL, $variableSymbol);

        return $this;
    }

    public function getQrCodeContent(int $size): string
    {
        $qrCode = $this->getQrCode();
        $qrCode->setSize($size);
        return $qrCode->writeString();
    }
}
