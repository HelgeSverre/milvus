<?php

namespace HelgeSverre\Milvus\Data;

use HelgeSverre\Milvus\Data\Support\Payload;

final readonly class MutationResult
{
    public function __construct(
        public ?int $insertCount,
        public array $insertIds,
        public ?int $upsertCount,
        public array $upsertIds,
        public ?int $deleteCount,
        public array $deleteIds,
        public array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            insertCount: Payload::optionalInteger($data, 'insertCount', 'data'),
            insertIds: Payload::listOfIds($data, 'insertIds', 'data'),
            upsertCount: Payload::optionalInteger($data, 'upsertCount', 'data'),
            upsertIds: Payload::listOfIds($data, 'upsertIds', 'data'),
            deleteCount: Payload::optionalInteger($data, 'deleteCount', 'data'),
            deleteIds: Payload::listOfIds($data, 'deleteIds', 'data'),
            raw: $data,
        );
    }

    public function affectedCount(): ?int
    {
        return $this->insertCount ?? $this->upsertCount ?? $this->deleteCount;
    }
}
