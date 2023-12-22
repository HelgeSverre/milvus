<?php

namespace HelgeSverre\Milvus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HelgeSverre\Milvus\Milvus
 */
class Milvus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \HelgeSverre\Milvus\Milvus::class;
    }
}
