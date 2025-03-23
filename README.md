# Short Payment Descriptor (SPayD) with QR output

PHP library designed to generate bank payments encoded as QR codes, making transactions faster and more convenient.

## Usage

Below is a simple example of generating a QR code for a payment order directly in template:

```php
use Money\Money;
use PetrKnap\SpaydQr\QrCode;
use PetrKnap\SpaydQr\Spayd;

echo '<img src="' . QrCode::asDataUri(
    Spayd::create(
        account: 'CZ7801000000000000000123',
        amount: Money::CZK(79950),
    ),
) . '">';
```

The library allows you to customize payment details, such as adding a variable symbol:

```php
use Money\Money;
use PetrKnap\SpaydQr\Spayd;

Spayd::create(
    account: 'CZ7801000000000000000123',
    amount: Money::CZK(79950),
)->withVariableSymbol(20250323001);
```

Instead of specifying `amount`, you can use `invoice` to define invoice:

```php
use Money\Money;
use PetrKnap\SpaydQr\Spayd;

Spayd::create(
    account: 'CZ7801000000000000000123',
    invoice: Spayd\Sind::create(
        id: 'FA20250323001',
        issueDate: new DateTime('2025-03-23'),
        amount: Money::CZK(79950),
    ),
)->withVariableSymbol(20250323001);
```

---

Run `composer require petrknap/spayd-qr` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
