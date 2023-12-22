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

This is the contents of the published `config/milvus.php` file:

```php
return [
    'token' => env('MILVUS_TOKEN'),
    'username' => env('MILVUS_USERNAME'),
    'password' => env('MILVUS_PASSWORD'),
    'host' => env('MILVUS_HOST', 'localhost'),
    'port' => env('MILVUS_PORT', '19530'),
];
```

## Usage

For Laravel users, you can use the `Milvus` facade to interact with the Milvus API:

```php
use HelgeSverre\Milvus\Facades\Milvus;

// Collection Operations using the facade
$listCollectionsResponse = Milvus::collections()->list('your-cluster-endpoint', 'your-db-name');
$createCollectionResponse = Milvus::collections()->create('collection-name', 128);
$describeCollectionResponse = Milvus::collections()->describe('collection-name');
$dropCollectionResponse = Milvus::collections()->drop('collection-name');

// Vector Operations using the facade
$insertVectorResponse = Milvus::vector()->insert('collection-name', ['vector-data']);
$searchVectorResponse = Milvus::vector()->search('collection-name', ['vector-query']);
$deleteVectorResponse = Milvus::vector()->delete('vector-id', 'collection-name');
$queryVectorResponse = Milvus::vector()->query('collection-name', 'filter-expression');
$getVectorResponse = Milvus::vector()->get('vector-id', 'collection-name');
$upsertVectorResponse = Milvus::vector()->upsert('collection-name', ['vector-data']);
```

```php
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;

$milvus = new Milvus(
    token: config('milvus.token'),
    host: config('milvus.host'),
    port: config('milvus.port')
);

// Models 
$list = $milvus->models()->list();

// Collection Operations
$collections = $milvus->collections();
$listCollectionsResponse = $collections->list('your-cluster-endpoint', 'your-db-name');
$createCollectionResponse = $collections->create('collection-name', 128);
$describeCollectionResponse = $collections->describe('collection-name');
$dropCollectionResponse = $collections->drop('collection-name');

// Vector Operations
$vectors = $milvus->vector();
$insertVectorResponse = $vectors->insert('collection-name', ['vector-data']);
$searchVectorResponse = $vectors->search('collection-name', ['vector-query']);
$deleteVectorResponse = $vectors->delete('vector-id', 'collection-name');
$queryVectorResponse = $vectors->query('collection-name', 'filter-expression');
$getVectorResponse = $vectors->get('vector-id', 'collection-name');
$upsertVectorResponse = $vectors->upsert('collection-name', ['vector-data']);
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

"Milvus®" and the Milvus logo are registered trademarks of
the [Linux Foundation](https://www.linuxfoundation.org/about) (LF Projects, LLC). This package is not affiliated with,
endorsed by, or sponsored by the Linux Foundation. It's developed independently and uses the "Milvus" name under fair
use, solely for identification. All trademarks and registered trademarks, including "Milvus®", are the property of their
respective owners. "Milvus®" is
a [registered trademark](https://branddb.wipo.int/en/quicksearch/brand/EM500000018660437) of the Linux Foundation.
