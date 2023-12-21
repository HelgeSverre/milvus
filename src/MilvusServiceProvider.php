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
            return new Milvus(
                apiKey: config('milvus.api_key'),
            );
        });
    }
}
