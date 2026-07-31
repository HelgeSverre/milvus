<?php

namespace HelgeSverre\Milvus\Data\Support;

use HelgeSverre\Milvus\Exceptions\InvalidResponseException;

final class Payload
{
    public static function requiredString(array $payload, string $key, string $path): string
    {
        $value = $payload[$key] ?? null;

        if (! is_string($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'string', $value);
        }

        return $value;
    }

    public static function optionalString(array $payload, string $key, string $path): ?string
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        if (! is_string($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'string', $value);
        }

        return $value;
    }

    public static function optionalBoolean(array $payload, string $key, string $path): ?bool
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        if (! is_bool($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'boolean', $value);
        }

        return $value;
    }

    public static function optionalInteger(array $payload, string $key, string $path): ?int
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        if (! is_int($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'integer', $value);
        }

        return $value;
    }

    public static function optionalIntegerOrString(array $payload, string $key, string $path): int|string|null
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        if (! is_int($value) && ! is_string($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'integer or string', $value);
        }

        return $value;
    }

    public static function optionalNumber(array $payload, string $key, string $path): int|float|null
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];

        if (! is_int($value) && ! is_float($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'number', $value);
        }

        return $value;
    }

    public static function requiredArray(array $payload, string $key, string $path): array
    {
        $value = $payload[$key] ?? null;

        if (! is_array($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'array or object', $value);
        }

        return $value;
    }

    public static function optionalArray(array $payload, string $key, string $path): array
    {
        if (! array_key_exists($key, $payload)) {
            return [];
        }

        $value = $payload[$key];

        if (! is_array($value)) {
            throw InvalidResponseException::expected(self::path($path, $key), 'array or object', $value);
        }

        return $value;
    }

    public static function listOfStrings(array $payload, string $key, string $path): array
    {
        $values = self::optionalArray($payload, $key, $path);
        self::assertList($values, self::path($path, $key));

        foreach ($values as $index => $value) {
            if (! is_string($value)) {
                throw InvalidResponseException::expected(
                    sprintf('%s.%d', self::path($path, $key), $index),
                    'string',
                    $value,
                );
            }
        }

        return $values;
    }

    public static function listOfArrays(array $payload, string $key, string $path): array
    {
        $values = self::optionalArray($payload, $key, $path);
        self::assertList($values, self::path($path, $key));

        foreach ($values as $index => $value) {
            if (! is_array($value)) {
                throw InvalidResponseException::expected(
                    sprintf('%s.%d', self::path($path, $key), $index),
                    'object',
                    $value,
                );
            }
        }

        return $values;
    }

    public static function listOfIds(array $payload, string $key, string $path): array
    {
        $values = self::optionalArray($payload, $key, $path);
        self::assertList($values, self::path($path, $key));

        foreach ($values as $index => $value) {
            if (! is_int($value) && ! is_string($value)) {
                throw InvalidResponseException::expected(
                    sprintf('%s.%d', self::path($path, $key), $index),
                    'integer or string',
                    $value,
                );
            }
        }

        return $values;
    }

    public static function listOfIntegers(array $payload, string $key, string $path): array
    {
        $values = self::optionalArray($payload, $key, $path);
        self::assertList($values, self::path($path, $key));

        foreach ($values as $index => $value) {
            if (! is_int($value)) {
                throw InvalidResponseException::expected(
                    sprintf('%s.%d', self::path($path, $key), $index),
                    'integer',
                    $value,
                );
            }
        }

        return $values;
    }

    public static function listOfNumbers(array $payload, string $key, string $path): array
    {
        $values = self::optionalArray($payload, $key, $path);
        self::assertList($values, self::path($path, $key));

        foreach ($values as $index => $value) {
            if (! is_int($value) && ! is_float($value)) {
                throw InvalidResponseException::expected(
                    sprintf('%s.%d', self::path($path, $key), $index),
                    'number',
                    $value,
                );
            }
        }

        return $values;
    }

    public static function assertList(array $value, string $path): void
    {
        if (! array_is_list($value)) {
            throw InvalidResponseException::expected($path, 'list', $value);
        }
    }

    private static function path(string $path, string $key): string
    {
        return $path === '' ? $key : "{$path}.{$key}";
    }
}
