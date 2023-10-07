<?php declare(strict_types=1);

namespace PetrKnap\SpaydQr;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Sunfox\Spayd\Spayd;

class SpaydQr implements SpaydQrInterface
{
    /** @internal */
    protected function __construct(
        /** @internal */ protected Spayd $spayd,
        private QrCode $qrCode,
        string $iban,
        Money $money
    ) {
        $spayd
            ->add(self::SPAYD_IBAN, $iban)
            ->add(self::SPAYD_AMOUNT, $this->getAmount($money))
            ->add(self::SPAYD_CURRENCY, $money->getCurrency()->getCode());
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

    public function setVariableSymbol(int $variableSymbol): self
    {
        $this->spayd->add(self::SPAYD_VARIABLE_SYMBOL, (string) $variableSymbol);

        return $this;
    }

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

    public function setWriter(WriterInterface $writer): self
    {
        $this->qrCode->setWriter($writer);

        return $this;
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

    /** @internal */
    protected function prepareQrCode(?Spayd $spayd, ?int $size, ?int $margin): QrCode
    {
        if ($spayd !== null) {
            $this->qrCode->setText($spayd->generate());
        }

        if ($size !== null) {
            $this->qrCode->setSize($size);
        }

        if ($margin !== null) {
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
