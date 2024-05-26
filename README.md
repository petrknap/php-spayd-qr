# Short Payment Descriptor (SPayD) with QR output

It connects [sunfoxcz/spayd-php] and [endroid/qr-code] to one unit.

## Example

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



[sunfoxcz/spayd-php]:https://github.com/sunfoxcz/spayd-php
[endroid/qr-code]:https://github.com/endroid/qr-code

---

Run `composer require petrknap/spayd-qr` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
