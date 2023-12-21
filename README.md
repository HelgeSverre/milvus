<p align="center"><img src="./art/header.png"></p>

# Milvus.io PHP API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)

[Milvus](https://github.com/milvus-io/milvus) is an open-source vector database that is highly flexible, reliable, and
blazing fast. It supports adding,
deleting, updating, and near real-time search of vectors on a trillion-byte scale.

This package provides an API Client based on the OpenAPI spec provided for
the [REST API](https://raw.githubusercontent.com/milvus-io/web-content/master/API_Reference/milvus-restful/v2.3.x/Restful%20API.openapi.json).

## Installation

You can install the package via composer:

```bash
composer require helgesverre/milvus
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="milvus-config"
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('MILVUS_API_KEY'),
    'base_url' => env('MILVUS_BASE_URL'),
];
```

## Usage

```php
use HelgeSverre\Milvus\Milvus;

$milvus = new Milvus(apiKey: config('milvus.api_key'));

// Models 
$list = $milvus->models()->list();

// todo: write docs
```

## Testing

```bash
cp .env.example .env
composer test
composer analyse src
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer

"Milvus®" and the Milvus logo are [trademarks](http://tmsearch.uspto.gov/bin/showfield?f=doc&state=4801:9uy7k2.2.1) of
the [Linux Foundation](https://www.linuxfoundation.org/about) (LF Projects, LLC). This package is not affiliated with,
endorsed by, or sponsored by the Linux Foundation. All trademarks and registered trademarks are the property of their
respective owners.
