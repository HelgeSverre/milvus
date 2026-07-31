<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Support\Payload;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;
use HelgeSverre\Milvus\Exceptions\MilvusApiException;
use Saloon\Http\Response;

abstract readonly class MilvusResponse
{
    public int $code;

    public ?string $message;

    public ?int $cost;

    public ?int $scannedRemoteBytes;

    public ?int $scannedTotalBytes;

    public int|float|null $cacheHitRatio;

    public array $raw;

    protected function __construct(ResponseMetadata $metadata)
    {
        $this->code = $metadata->code;
        $this->message = $metadata->message;
        $this->cost = $metadata->cost;
        $this->scannedRemoteBytes = $metadata->scannedRemoteBytes;
        $this->scannedTotalBytes = $metadata->scannedTotalBytes;
        $this->cacheHitRatio = $metadata->cacheHitRatio;
        $this->raw = $metadata->raw;
    }

    abstract public static function fromResponse(Response $response): static;

    public function successful(): bool
    {
        return $this->code === 0;
    }

    public function throwIfFailed(): static
    {
        if (! $this->successful()) {
            throw new MilvusApiException(
                milvusCode: $this->code,
                message: $this->message ?? "Milvus API request failed with code {$this->code}.",
            );
        }

        return $this;
    }

    protected static function metadata(Response $response): ResponseMetadata
    {
        return ResponseMetadata::fromResponse($response);
    }

    protected static function data(ResponseMetadata $metadata): array
    {
        if (! array_key_exists('data', $metadata->raw)) {
            if ($metadata->successful()) {
                throw InvalidResponseException::expected(
                    'data',
                    'array or object',
                    null,
                );
            }

            return [];
        }

        return Payload::requiredArray($metadata->raw, 'data', '');
    }
}
