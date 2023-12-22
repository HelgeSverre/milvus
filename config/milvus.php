<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Milvus SDK Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file is for interfacing with the Milvus REST API using
    | the Milvus SDK. Authentication can be achieved either through an API token
    | or via a combination of username and password. The API token is the preferred
    | method, and if provided, username and password are not required.
    |
    | 'host' and 'port' settings determine the connection details for the
    | Milvus REST API, with default values set to 'localhost' and '19530',
    |
    */

    'token' => env('MILVUS_TOKEN'),
    'username' => env('MILVUS_USERNAME'),
    'password' => env('MILVUS_PASSWORD'),
    'host' => env('MILVUS_HOST', 'localhost'),
    'port' => env('MILVUS_PORT', '19530'),

];
