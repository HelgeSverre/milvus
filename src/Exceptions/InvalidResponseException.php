<?php

namespace HelgeSverre\Milvus\Exceptions;

use JsonException;
use UnexpectedValueException;

class InvalidResponseException extends UnexpectedValueException
{
    public static function malformedJson(JsonException $exception): self
    {
        return new self('Milvus returned malformed JSON.', previous: $exception);
    }

    public static function expected(string $path, string $expected, mixed $actual): self
    {
        return new self(sprintf(
            'Invalid Milvus response at "%s": expected %s, got %s.',
            $path,
            $expected,
            get_debug_type($actual),
        ));
    }
}
