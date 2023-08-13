# Short Payment Descriptor (SPayD) with QR output

It connects [shoptet/spayd-php] and [endroid/qr-code] to one unit.

## Example

```php
<img src="<?=

    PetrKnap\Php\SpaydQr\SpaydQr::create(
        'CZ7801000000000000000123',
        Money\Money::CZK(79950)
    )->getDataUri();

?>" />
```


## How to install

Run `composer require petrknap/php-spaydqr` or merge this JSON code with your project `composer.json` file manually and run `composer install`. Instead of `dev-master` you can use [one of released versions].

```json
{
    "require": {
        "petrknap/php-spaydqr": "dev-master"
    }
}
```

Or manually clone this repository via `git clone https://github.com/petrknap/php-spaydqr.git` or download [this repository as ZIP] and extract files into your project.



[one of released versions]:https://github.com/petrknap/php-spaydqr/releases
[this repository as ZIP]:https://github.com/petrknap/php-spaydqr/archive/master.zip




[shoptet/spayd-php]:https://github.com/shoptet/spayd-php
[endroid/qr-code]:https://github.com/endroid/qr-code

---

You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
