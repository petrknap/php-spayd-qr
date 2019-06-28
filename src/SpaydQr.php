<?php

namespace PetrKnap\Php\SpaydQr;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Shoptet\Spayd\Spayd;

class SpaydQr
{
    const SPAYD_IBAN = 'ACC';
    const SPAYD_AMOUNT = 'AM';
    const SPAYD_CURRENCY = 'CC';
    const SPAYD_VARIABLE_SYMBOL = 'X-VS';
    const SPAYD_INVOICE = 'X-INV';
    const SPAYD_INVOICE_FORMAT = 'SID';
    const SPAYD_INVOICE_VERSION = '1.0';
    const SPAYD_INVOICE_ID = 'ID';
    const SPAYD_INVOICE_ISSUE_DATE = 'DD';
    const SPAYD_INVOICE_SELLER_IDENTIFICATION_NUMBER = 'INI';
    const SPAYD_INVOICE_SELLER_VAT_IDENTIFICATION_NUMBER = 'VII';
    const SPAYD_INVOICE_BUYER_IDENTIFICATION_NUMBER = 'INR';
    const SPAYD_INVOICE_BUYER_VAT_IDENTIFICATION_NUMBER = 'VIR';
    const SPAYD_INVOICE_MESSAGE = 'MSG';

    const QR_SIZE = 300;
    const QR_MARGIN = 0;

    private $spayd;

    private $qrCode;

    public function __construct(
        Spayd $spayd,
        QrCode $qrCode,
        string $iban,
        Money $money
    ) {
        $this->spayd = $spayd
            ->add(self::SPAYD_IBAN, $iban)
            ->add(self::SPAYD_AMOUNT, $this->getAmount($money))
            ->add(self::SPAYD_CURRENCY, $money->getCurrency()->getCode());

        $this->qrCode = $qrCode;
    }

    public static function create(string $iban, Money $money, WriterInterface $writer = null): self
    {
        $qrCode = new QrCode();
        $qrCode->setWriter($writer ?: new PngWriter());

        return new self(
            new Spayd(),
            $qrCode,
            $iban,
            $money
        );
    }

    public function setWriter(WriterInterface $writer): self
    {
        $this->qrCode->setWriter($writer);

        return $this;
    }

    public function setVariableSymbol(int $variableSymbol): self
    {
        $this->spayd->add(self::SPAYD_VARIABLE_SYMBOL, $variableSymbol);

        return $this;
    }

    /**
     * @see https://qr-faktura.cz/
     */
    public function setInvoice(
        string $id,
        \DateTimeInterface $issueDate,
        int $sellerIdentificationNumber,
        ?string $sellerVatIdentificationNumber,
        ?int $buyerIdentificationNumber,
        ?string $buyerVatIdentificationNumber,
        ?string $description
    ): self {
        $normalize = function (string $input): string {
            return str_replace(
                ['*', '%2A', '%2a'],
                ['' , ''   , ''   ],
                $input
            );
        };

        $invoice = [
            self::SPAYD_INVOICE_FORMAT, self::SPAYD_INVOICE_VERSION,
            self::SPAYD_INVOICE_ID . ':' . $normalize($id),
            self::SPAYD_INVOICE_ISSUE_DATE . ':' . $issueDate->format('Ymd'),
            self::SPAYD_INVOICE_SELLER_IDENTIFICATION_NUMBER . ':' . $sellerIdentificationNumber,
            $sellerVatIdentificationNumber ? self::SPAYD_INVOICE_SELLER_VAT_IDENTIFICATION_NUMBER . ':' . $normalize($sellerVatIdentificationNumber) : null,
            $buyerIdentificationNumber ? self::SPAYD_INVOICE_BUYER_IDENTIFICATION_NUMBER . ':' . $buyerIdentificationNumber : null,
            $buyerVatIdentificationNumber ? self::SPAYD_INVOICE_BUYER_VAT_IDENTIFICATION_NUMBER . ':' . $normalize($buyerVatIdentificationNumber) : null,
            $description ? self::SPAYD_INVOICE_MESSAGE . ':' . $normalize($description) : null,
        ];

        $this->spayd->add(self::SPAYD_INVOICE, implode('%2A', array_filter($invoice)));

        return $this;
    }

    #region Output
    public function getSpayd(): Spayd
    {
        return $this->spayd;
    }

    public function getQrCode(): QrCode
    {
        return $this->prepareQrCode($this->spayd, null, null);
    }

    public function getContentType(): string
    {
        return $this->prepareQrCode(null, null, null)->getContentType();
    }

    public function getContent(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->prepareQrCode($this->spayd, $size, $margin)->writeString();
    }

    public function getDataUri(int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): string
    {
        return $this->prepareQrCode($this->spayd, $size, $margin)->writeDataUri();
    }

    public function writeFile(string $path, int $size = self::QR_SIZE, int $margin = self::QR_MARGIN): void
    {
        $this->prepareQrCode($this->spayd, $size, $margin)->writeFile($path);
    }
    #endregion

    private function prepareQrCode(?Spayd $spayd, ?int $size, ?int $margin): QrCode
    {
        if (null !== $spayd) {
            $this->qrCode->setText($spayd->generate());
        }

        if (null !== $size) {
            $this->qrCode->setSize($size);
        }

        if (null !== $margin) {
            $this->qrCode->setMargin($margin);
        }

        return $this->qrCode;
    }

    private function getAmount(Money $money): string
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return $moneyFormatter->format($money);
    }
}
