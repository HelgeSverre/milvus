<?php

use HelgeSverre\Milvus\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function createTestVector(float $value = 0.1, int $dimension = 128): array
{
    return array_fill(0, $dimension, $value);
}
