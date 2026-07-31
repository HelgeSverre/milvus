<?php

namespace HelgeSverre\Milvus\Exceptions;

use RuntimeException;

class MilvusApiException extends RuntimeException
{
    public function __construct(
        public readonly int $milvusCode,
        string $message,
    ) {
        parent::__construct($message, $milvusCode);
    }
}
