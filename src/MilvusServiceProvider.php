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
        $token = $this->configuredString('milvus.token');

        if ($token !== null) {
            return $token;
        }

        $username = $this->configuredString('milvus.username');
        $password = $this->configuredString('milvus.password');

        if ($username === null || $password === null) {
            return null;
        }

        return sprintf('%s:%s', $username, $password);
    }

    private function configuredString(string $key): ?string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
