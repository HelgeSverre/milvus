<?php

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('resource classes extend the base resource')
    ->expect('HelgeSverre\Milvus\Resource')
    ->toExtend('Saloon\Http\BaseResource');

it('request classes extend the Saloon request class')
    ->expect('HelgeSverre\Milvus\Requests')
    ->classes()
    ->toExtend('Saloon\Http\Request');
