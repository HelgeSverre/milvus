<?php

namespace HelgeSverre\Milvus\Requests;

use HelgeSverre\Milvus\Data\Response\MilvusResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

abstract class MilvusRequest extends Request
{
    public function createDtoFromResponse(Response $response): MilvusResponse
    {
        $responseDto = $this->responseDto();

        return $responseDto::fromResponse($response);
    }

    /** @return class-string<MilvusResponse> */
    abstract protected function responseDto(): string;
}
