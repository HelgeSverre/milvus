<?php

namespace HelgeSverre\Milvus\Data\Response;

use HelgeSverre\Milvus\Data\Support\Payload;
use HelgeSverre\Milvus\Exceptions\InvalidResponseException;
use JsonException;
use Saloon\Http\Response;
use stdClass;

final readonly class ResponseMetadata
{
    private function __construct(
        public int $code,
        public ?string $message,
        public ?int $cost,
        public ?int $scannedRemoteBytes,
        public ?int $scannedTotalBytes,
        public int|float|null $cacheHitRatio,
        public array $raw,
    ) {}

    public static function fromResponse(Response $response): self
    {
        try {
            $decoded = json_decode(
                $response->body(),
                false,
                512,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING,
            );
        } catch (JsonException $exception) {
            throw InvalidResponseException::malformedJson($exception);
        }

        if (! $decoded instanceof stdClass) {
            throw InvalidResponseException::expected('$', 'JSON object', $decoded);
        }

        $payload = self::normalizeObject($decoded);

        $code = $payload['code'] ?? null;

        if (! is_int($code)) {
            throw InvalidResponseException::expected('code', 'integer', $code);
        }

        return new self(
            code: $code,
            message: Payload::optionalString($payload, 'message', ''),
            cost: Payload::optionalInteger($payload, 'cost', ''),
            scannedRemoteBytes: Payload::optionalInteger($payload, 'scanned_remote_bytes', ''),
            scannedTotalBytes: Payload::optionalInteger($payload, 'scanned_total_bytes', ''),
            cacheHitRatio: Payload::optionalNumber($payload, 'cache_hit_ratio', ''),
            raw: $payload,
        );
    }

    public function successful(): bool
    {
        return $this->code === 0;
    }

    private static function normalizeObject(stdClass $object): array
    {
        $normalized = [];

        foreach (get_object_vars($object) as $key => $value) {
            $normalized[$key] = self::normalizeValue($value);
        }

        return $normalized;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            return self::normalizeObject($value);
        }

        if (is_array($value)) {
            return array_map(self::normalizeValue(...), $value);
        }

        return $value;
    }
}
