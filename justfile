# List recipes.
default:
    @just --list

# Install dependencies.
[group('setup')]
install:
    composer install --prefer-dist --no-interaction --no-progress

# Run all tests.
[group('test')]
test: unit integration

# Run unit tests.
[group('test')]
unit:
    composer test:unit

# Run test coverage.
[group('test')]
coverage:
    #!/usr/bin/env bash
    set -euo pipefail

    if command -v herd >/dev/null 2>&1; then
        herd coverage ./vendor/bin/pest tests/Unit tests/ArchTest.php --coverage
    else
        ./vendor/bin/pest tests/Unit tests/ArchTest.php --coverage
    fi

# Run PHPStan.
[group('quality')]
analyse:
    composer analyse -- src

# Format PHP.
[group('quality')]
format:
    composer format

# Check formatting.
[group('quality')]
format-check:
    composer format:test

# Validate Composer files.
[group('quality')]
validate:
    composer validate --strict

# Audit dependencies.
[group('quality')]
audit:
    composer audit

# Run fast checks.
[group('quality')]
check: validate audit format-check analyse unit

# Start Milvus.
[group('docker')]
milvus-up $MILVUS_VERSION="3.0.0":
    MILVUS_IMAGE="milvusdb/milvus:v${MILVUS_VERSION}" docker compose up -d --wait --wait-timeout 180

# Show containers.
[group('docker')]
milvus-status:
    docker compose ps

# Follow Milvus logs.
[group('docker')]
milvus-logs:
    docker compose logs --follow standalone

# Stop Milvus.
[group('docker')]
milvus-down:
    docker compose down

# Run live tests.
[group('test')]
integration $MILVUS_VERSION="3.0.0": (milvus-up MILVUS_VERSION)
    composer test:integration

# Run release gate.
[group('quality')]
release-check: check integration
