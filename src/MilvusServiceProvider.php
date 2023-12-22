<?php

namespace HelgeSverre\Milvus;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MilvusServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('milvus')->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->app->bind(Milvus::class, function () {
            $token = config('milvus.token') ?: base64_encode(
                sprintf('%s:%s', config('milvus.username'), config('milvus.password'))
            );

            // TODO: Trow exception if none are defined

            return new Milvus(
                token: $token,
                host: config('milvus.host'),
                port: config('milvus.port'),
            );
        });
    }
}
