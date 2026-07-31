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
                token: $this->resolveToken(),
                host: config('milvus.host'),
                port: config('milvus.port'),
            );
        });
    }

    private function resolveToken(): ?string
    {
        $token = config('milvus.token');

        if (is_string($token) && $token !== '') {
            return $token;
        }

        $username = config('milvus.username');
        $password = config('milvus.password');

        if (! is_string($username) || $username === '' || ! is_string($password) || $password === '') {
            return null;
        }

        return "{$username}:{$password}";
    }
}
