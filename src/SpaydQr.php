<?php

namespace PetrKnap\Php\SpaydQr;

use Endroid\QrCode\QrCode;
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
        string $currency,
        int $size
    ) {
        $this->spayd = $spayd
            ->add(static::IBAN, $iban)
            ->add(static::AMOUNT, sprintf('%.2f', $amount))
            ->add(static::CURRENCY, $currency);

        $this->qrCode = $qrCode;
        $this->qrCode->setSize($size);
    }

    public static function create(string $iban, float $amount, string $currency, int $size): self
    {
        return new self(
            new Spayd(),
            new QrCode(),
            $iban,
            $amount,
            $currency,
            $size
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

    public function getQrCodeContent(): string
    {
        return $this->getQrCode()->writeString();
    }
}
