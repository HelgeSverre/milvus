<p align="center"><img src="./art/header.png"></p>

# Milvus.io PHP API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/milvus.svg?style=flat-square)](https://packagist.org/packages/helgesverre/milvus)

[Milvus](https://github.com/milvus-io/milvus) is an open-source vector database that is highly flexible, reliable, and
blazing fast. It supports adding,
deleting, updating, and near real-time search of vectors on a trillion-byte scale.

This package is an API Client for the Milvus v2.3.3 Restful API, and is built on the [Saloon](https://docs.saloon.dev/)
package.

Documentation about the Restful API is available on
the [Milvus website](https://milvus.io/api-reference/restful/v2.3.x/About.md), and an OpenAPI spec is
available [here](https://raw.githubusercontent.com/milvus-io/web-content/master/API_Reference/milvus-restful/v2.3.x/Restful%20API.openapi.json).

## Versions

| Milvus Version | PHP Client Version |
|----------------|--------------------|
| v2.3.x         | v0.0.x             |
| v2.2.x         | Not supported (*)  |

(*) But is mostly compatible, the only difference (that I can see) between them is the new Vector Upsert endpoint, and
new parameters (`params.range_filter` and `params.radius`)  in the Vector Search endpoint.

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

// NOTE: dbName is optional and defaults to 'default', this is only relevant if you have multiple databases.
// List all collections in the 'default' database
Milvus::collections()->list(
    dbName: 'default'
);

// Create a new collection named 'documents' in the 'default' database with a specified dimension
Milvus::collections()->create(
    collectionName: 'documents',
    dimension: 128,
    dbName: 'default',
);

// Describe the structure and properties of the 'documents' collection in the 'default' database
Milvus::collections()->describe(
    collectionName: 'documents',
    dbName: 'default',
);

// Drop or delete the 'documents' collection from the 'default' database
Milvus::collections()->drop(
    collectionName: 'documents',
    dbName: 'default',
);


// Insert a new vector into the 'documents' collection with additional fields like title and link
// Note "vector" is a reserved field name and must be used for the vector data
Milvus::vector()->insert(
    collectionName: 'documents',
    data: [
        'vector' => [0.1, 0.2, 0.3 /* etc... */],
        "title" => "Document name here",
        "link" => "https://example.com/document-name-here",
    ]
);

// Search for similar vectors in the 'documents' collection using a provided vector
Milvus::vector()->search(
    collectionName: 'documents',
    vector: [0.1, 0.2, 0.3 /* etc... */],
);

// Delete a vector from the 'documents' collection using its ID
Milvus::vector()->delete(
    id: '123129471497',
    collectionName: 'documents'
);

// Query the 'documents' collection for specific documents using a filter condition and select specific output fields
Milvus::vector()->query(
    collectionName: 'documents',
    filter: "id in [443300716234671427, 443300716234671426]",
    outputFields: ["id", "title", "link"],
);

// Retrieve a specific vector from the 'documents' collection using its ID
Milvus::vector()->get(
    id: '123129471497',
    collectionName: 'documents'
);

// Update or insert a vector in the 'documents' collection. If the ID exists, it's updated; if not, a new entry is created
Milvus::vector()->upsert(
    collectionName: 'documents',
    data: [
        'id' => 123129471497,
        'vector' => [0.1, 0.2, 0.3 /* etc... */],
        "title" => "Document name here",
        "link" => "https://example.com/document-name-here",
    ]
);

```

### Without Laravel

If you are not using laravel, you will have to create a new instance of the Milvus class and provide a token or
user/pass, the host and the port.

```php
<?php
// use HelgeSverre\Milvus\Facades\Milvus;
use HelgeSverre\Milvus\Milvus;

$milvus = new Milvus(
    token: "your-token",
    host: "localhost",
    port: "19530"
);


// Import the Milvus facade for easier access to Milvus functions

// NOTE: dbName is optional and defaults to 'default', this is only relevant if you have multiple databases.
// List all collections in the 'default' database
$milvus->collections()->list(
    dbName: 'default'
);

// Create a new collection named 'documents' in the 'default' database with a specified dimension
$milvus->collections()->create(
    collectionName: 'documents',
    dimension: 128,
    dbName: 'default',
);

// Describe the structure and properties of the 'documents' collection in the 'default' database
$milvus->collections()->describe(
    collectionName: 'documents',
    dbName: 'default',
);

// Drop or delete the 'documents' collection from the 'default' database
$milvus->collections()->drop(
    collectionName: 'documents',
    dbName: 'default',
);


// Insert a new vector into the 'documents' collection with additional fields like title and link
// Note "vector" is a reserved field name and must be used for the vector data
$milvus->vector()->insert(
    collectionName: 'documents',
    data: [
        'vector' => [0.1, 0.2, 0.3 /* etc... */],
        "title" => "Document name here",
        "link" => "https://example.com/document-name-here",
    ]
);

// Search for similar vectors in the 'documents' collection using a provided vector
$milvus->vector()->search(
    collectionName: 'documents',
    vector: [0.1, 0.2, 0.3 /* etc... */],
);

// Delete a vector from the 'documents' collection using its ID
$milvus->vector()->delete(
    id: '123129471497',
    collectionName: 'documents'
);

// Query the 'documents' collection for specific documents using a filter condition and select specific output fields
$milvus->vector()->query(
    collectionName: 'documents',
    filter: "id in [443300716234671427, 443300716234671426]",
    outputFields: ["id", "title", "link"],
);

// Retrieve a specific vector from the 'documents' collection using its ID
$milvus->vector()->get(
    id: '123129471497',
    collectionName: 'documents'
);

// Update or insert a vector in the 'documents' collection. If the ID exists, it's updated; if not, a new entry is created
$milvus->vector()->upsert(
    collectionName: 'documents',
    data: [
        'id' => 123129471497,
        'vector' => [0.1, 0.2, 0.3 /* etc... */],
        "title" => "Document name here",
        "link" => "https://example.com/document-name-here",
    ]
);

```

### Using with Zilliz Cloud

If you are using the hosted version of Milvus, you will need to specify the following host and port along with your API
token:

```php
use HelgeSverre\Milvus\Milvus;

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

## Running Milvus in Docker

To quickly get started with Milvus, you can run it in Docker, by using the following command

```bash
# Download the docker-compose.yml file
wget https://github.com/milvus-io/milvus/releases/download/v2.3.3/milvus-standalone-docker-compose.yml -O docker-compose.yml

# Start Milvus
docker compose up -d
```

A healthcheck endpoint will now be available on `http://localhost:9091/healthz`, and the Milvus API will be available
on `http://localhost:19530`.

To stop Milvus, run `docker compose down`, to wipe all the data, run `docker compose down -v`.

For more
details [Installing Milvus Standalone with Docker Compose](https://milvus.io/docs/install_standalone-docker.md)

For production workloads, consider checking out [Zilliz.com](https://zilliz.com/), which are the developers behind
Milvus and provides a hosted version of Milvus in the Cloud ☁️.

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
