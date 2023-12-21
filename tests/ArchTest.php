<?php

it('can test', fn () => expect(true)->toBeTrue());

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('All resource classes extend the base resource')
    ->expect('HelgeSverre\Milvus\Resource')
    ->toExtend('Saloon\Http\BaseResource');

it('All request classes extend the saloon request class')
    ->expect('HelgeSverre\Milvus\Requests')
    ->classes()
    ->toExtend('Saloon\Http\Request');
