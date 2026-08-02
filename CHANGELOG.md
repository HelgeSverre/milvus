# Changelog

All notable changes to this project are documented in this file. This project follows
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.3.1] - 2026-08-02

### Changed

- Raise the PHPStan quality gate from rule level 0 to level 5.
- Use disposable Docker storage for integration tests and version-specific storage for manual Milvus runs.

### Fixed

- Prevent stale metadata from one Milvus version from breaking later local integration runs against another version.
- Keep the documented client compatibility range and release-note references current for the 0.3 release line.

## [0.3.0] - 2026-07-31

### Added

- Database create, list, describe, and drop operations through `Milvus::databases()`.
- Typed database list, description, and property DTOs with int64-safe IDs and preserved future fields.
- Live Docker coverage for a full-client smoke test, database properties, custom schemas, AutoID, scalar-filtered
  search, and API failures across Milvus 2.5, 2.6, and 3.0.
- Automated Codecov reporting for the unit and architecture test suite.
- README guides for database management, filtered search, custom schemas, AutoID, and Zilliz REST v2 configuration.

### Changed

- `just test` runs both unit and Docker integration tests; use `just unit` for the fast suite.
- The `delete(id: ...)` convenience argument now emits the REST API's required primary-key filter instead of an
  unsupported `id` request field.
- Empty list request bodies retain JSON encoding errors while serializing as objects required by Milvus.

## [0.2.0] - 2026-07-31

### Added

- Compatibility with the shared Milvus REST v2 endpoints in Milvus 2.5, 2.6, and 3.0.
- Custom collection schemas, index parameters, collection descriptions, and collection-level parameters.
- Milvus 3 support for partial inserts and upserts, expression parameters, consistency levels, function scoring,
  search parameters, and searches using entity IDs.
- Typed and validated DTOs for every response family, including collection fields, indexes, functions, mutation
  results, dynamic entities, search metadata, API error envelopes, and int64-safe IDs.

### Changed

- The minimum PHP version is now 8.3.
- Saloon has been upgraded from version 3 to version 4.
- Laravel 12 and 13 are the actively supported framework versions. Laravel 10 and 11 remain covered as legacy,
  end-of-life compatibility targets.
- `autoID` now accepts a boolean instead of a string.
- Query and delete filters are optional to support the latest Milvus request forms.
- The stale bundled OpenAPI snapshot has been replaced by links to the maintained Milvus specifications.

### Fixed

- Preserve `false`, `0`, and empty-string values when serializing request bodies.
- Do not create an authenticator when no token or complete username/password pair is configured.
- Use the raw `username:password` Milvus token format and prefer an explicitly configured token.
- Decode documented Milvus 2.5–3.0 response variations without losing unknown or dynamic fields.

[Unreleased]: https://github.com/HelgeSverre/milvus/compare/v0.3.1...HEAD
[0.3.1]: https://github.com/HelgeSverre/milvus/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/HelgeSverre/milvus/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/HelgeSverre/milvus/compare/v0.1.0...v0.2.0
