<?php

namespace HelgeSverre\Milvus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\Milvus\Mistral
 */
class Milvus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\Milvus\Milvus::class;
    }
}
