# List available recipes when `just` is run without arguments.
default:
    @just --list

# Install the locked Composer dependencies.
install:
    composer install --prefer-dist --no-interaction --no-progress

# Run the fast unit and architecture test suite.
test:
    composer test:unit

# Run PHPStan against the package source.
analyse:
    composer analyse -- src

# Format the PHP source and tests.
format:
    composer format

# Check formatting without changing files.
format-check:
    composer format:test

# Validate composer.json and composer.lock.
validate:
    composer validate --strict

# Audit the locked dependencies for known vulnerabilities.
audit:
    composer audit

# Run every fast release-quality check.
check: validate audit format-check analyse test

# Start Milvus and wait for it to become healthy.
milvus-up $MILVUS_VERSION="3.0.0":
    MILVUS_IMAGE="milvusdb/milvus:v${MILVUS_VERSION}" docker compose up -d --wait --wait-timeout 180

# Show the current Docker service state.
milvus-status:
    docker compose ps

# Follow the Milvus Docker logs.
milvus-logs:
    docker compose logs --follow standalone

# Stop Milvus without deleting its data.
milvus-down:
    docker compose down

# Start a Milvus version and run both live lifecycle tests.
integration $MILVUS_VERSION="3.0.0": (milvus-up MILVUS_VERSION)
    composer test:integration

# Run the complete local release gate against Milvus 3.0.0.
release-check: check integration
