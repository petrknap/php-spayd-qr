# Short Payment Descriptor (SPayD) with QR output

It connects [shoptet/spayd-php] and [endroid/qr-code] to one unit.

## Example

```html
<img src="<?=

    PetrKnap\SpaydQr\SpaydQr::create(
        'CZ7801000000000000000123',
        Money\Money::CZK(79950)
    )->getDataUri();

?>" />
```



[shoptet/spayd-php]:https://github.com/shoptet/spayd-php
[endroid/qr-code]:https://github.com/endroid/qr-code

---

Run `composer require petrknap/spayd-qr` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
