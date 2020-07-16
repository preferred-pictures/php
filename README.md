# Preferred.pictures PHP Client Library

The [Preferred.pictures](https://preferred.pictures) PHP library provides a convenient way to call the
[Preferred.pictures](https://preferred.pictures) API for applications written in PHP

## Installation

```
composer require preferred-pictures/client
```

To use the bindings, use Composer's autoload:

```php
require_once('vendor/autoload.php')
```

## Usage

The package needs to be configured with your account's identity and
secret key, which is available in the Preferred.pictures interface.

```php


$client = new Preferred\-Pictures\Client("testidentity", "secret123456");

$url = $client->createChooseUrl(
    ["red", "green", "blue"],
    "test-tournament",
    300,
    6000,
    "https://www.example.com/image-",
    ".jpg"
);

# The url returned will appear to be something like:
#
# https://api.preferred.pictures/choose-url?choices=red%2Cgreen%2Cblue&tournament=testing&expiration=[EXPIRATION]&uid=[UNIQUEID]&ttl=600&prefix=https%3A%2F%2Fexample.com%2Fjacket-&suffix=.jpg&identity=test-identity&signature=[SIGNATURE]
#
# which should be placed where it is needed in your application or templates.
```
