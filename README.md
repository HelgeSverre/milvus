<p align="center"><img src="./art/header.png"></p>

# Milvus.io PHP API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)

[Milvus](https://github.com/milvus-io/milvus) is an open-source vector database that is highly flexible, reliable, and
blazing fast. It supports adding,
deleting, updating, and near real-time search of vectors on a trillion-byte scale.

This package provides an API Client based on the OpenAPI spec provided for
the [REST API](https://raw.githubusercontent.com/milvus-io/web-content/master/API_Reference/milvus-restful/v2.3.x/Restful%20API.openapi.json)
and the information found in this [documentation](https://milvus.io/api-reference/restful/v2.3.x/About.md).

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

### With Laravel

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

### Without Laravel

If you are not using laravel, you will have to create a new instance of the Milvus class and provide a token or
user/pass, the host and the port.

```php
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;

$milvus = new Milvus(
    token: "your-token",
    host: "localhost",
    port: "19530"
);


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

### Using with Zilliz.com (Hosted Milvus in the clOUD)

If you are using the hosted version of Milvus, you will need to specify the following host and port along with your API
token:

```php
use HelgeSverre\Milvus\Milvus;
use HelgeSverre\Milvus\Resource\CollectionOperations;
use HelgeSverre\Milvus\Resource\VectorOperations;

$milvus = new Milvus(
    token: "db_randomstringhere:passwordhere",
    host: 'https://in03-somerandomstring.api.gcp-us-west1.zillizcloud.com',
    port: '443'
);
```

## Example: Semantic Search with Milvus and OpenAI Embeddings

This example demonstrates how to perform a semantic search in Milvus using embeddings generated from OpenAI.

### Prepare Your Data

First, create an array of data you wish to index. In this example, we'll use blog posts with titles, summaries, and
tags.

```php
$blogPosts = [
    [
        'title' => 'Exploring Laravel',
        'summary' => 'A deep dive into Laravel frameworks...',
        'tags' => ['PHP', 'Laravel', 'Web Development']
    ],
       [
        'title' => 'Exploring Laravel',
        'summary' => 'A deep dive into Laravel frameworks, exploring its features and benefits for modern web development.',
        'tags' => ['PHP', 'Laravel', 'Web Development']
    ],
    [
        'title' => 'Introduction to React',
        'summary' => 'Understanding the basics of React and how it revolutionizes frontend development.',
        'tags' => ['JavaScript', 'React', 'Frontend']
    ],
    [
        'title' => 'Getting Started with Vue.js',
        'summary' => 'A beginner’s guide to building interactive web interfaces with Vue.js.',
        'tags' => ['JavaScript', 'Vue.js', 'Frontend']
    ],
];
```

### Generate Embeddings

Use OpenAI's embeddings API to convert the summaries of your blog posts into vector embeddings.

```php
$summaries = array_column($blogPosts, 'summary');
$embeddingsResponse = OpenAI::client('sk-your-openai-api-key')
    ->embeddings()
    ->create([
        'model' => 'text-embedding-ada-002',
        'input' => $summaries,
    ]);

foreach ($embeddingsResponse->embeddings as $embedding) {
    $blogPosts[$embedding->index]['vector'] = $embedding->embedding;
}
```

### Create Milvus collection

Create a collection in Milvus to store your blog post embeddings, note that the dimension of the embeddings must match
the dimension of the embeddings generated by OpenAI (`1536` if you are using the `text-embedding-ada-002` model).

```php
$milvus = new Milvus(
    token: "your-token",
    host: "localhost",
    port: "19530"
);


$milvus->collections()->create(
    collectionName: 'blog_posts',
    dimension: 1536,
);
```

### Insert into Milvus

Insert these embeddings, along with other blog post data, into your Milvus collection.

```php

$insertResponse = $milvus->vector()->insert('blog_posts', $blogPosts);
```

### Creating a Search Vector with OpenAI

Generate a search vector for your query, akin to how you processed the blog posts.

```php
$searchVectorResponse = OpenAI::client('sk-your-openai-api-key')
    ->embeddings()
    ->create([
        'model' => 'text-embedding-ada-002',
        'input' => 'laravel framework',
    ]);

$searchEmbedding = $searchVectorResponse->embeddings[0]->embedding;
```

### Searching using the Embedding in Milvus

Use the Milvus client to perform a search with the generated embedding.

```php
$searchResponse = $milvus->vector()->search(
    collectionName: 'blog_posts',
    vector: $searchEmbedding,
    limit: 3,
    outputFields: ['title', 'summary', 'tags']
);

// Output the search results
foreach ($searchResponse as $result) {
    echo "Title: " . $result['title'] . "\n";
    echo "Summary: " . $result['summary'] . "\n";
    echo "Tags: " . implode(', ', $result['tags']) . "\n\n";
}
```

## Testing

```bash
cp .env.example .env

## Start a local Milvus instance, it takes awhile to boot up
docker compose up -d
 
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
