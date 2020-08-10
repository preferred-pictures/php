# PreferredPictures PHP Client Library

The [PreferredPictures](https://preferred.pictures) PHP library provides a convenient way to call the
[PreferredPictures](https://preferred.pictures) API for applications written in PHP.

[View the full documentation about the PreferredPicture's API](https://docs.preferred.pictures/api-sdks/api)

[Learn more about what PreferredPictures can do.](https://docs.preferred.pictures/)

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
secret key, which is available in the PreferredPictures interface.

```php


$client = new PreferredPictures\Client("testidentity", "secret123456");

# Create a basic URL to pick between three different images.
$url = $client->createChooseUrl(
    ["https://www.example.com/image-red.jpg",
     "https://www.example.com/image-green.jpg",
     "https://www.example.com/image-blue.jpg"],
    "test-tournament",
);

# Use a prefix and suffix to make specifying the options less verbose
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
# https://api.preferred-pictures.com/choose-url?choices=red%2Cgreen%2Cblue&tournament=testing&expiration=[EXPIRATION]&uid=[UNIQUEID]&ttl=600&prefix=https%3A%2F%2Fexample.com%2Fjacket-&suffix=.jpg&identity=test-identity&signature=[SIGNATURE]
#
# which should be placed where it is needed in your application or templates.
```
