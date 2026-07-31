# Changelog

All notable changes to this project are documented in this file. This project follows
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-07-31

### Added

- Compatibility with the shared Milvus REST v2 endpoints in Milvus 2.5, 2.6, and 3.0.
- Custom collection schemas, index parameters, collection descriptions, and collection-level parameters.
- Milvus 3 support for partial inserts and upserts, expression parameters, consistency levels, function scoring,
  search parameters, and searches using entity IDs.
- Request-contract tests for every supported endpoint and public-resource forwarding tests for every client method.
- A Docker-backed lifecycle test covering collection creation, insert, get, query, vector and ID search, upsert,
  partial update, delete, and cleanup.
- CI matrices for PHP 8.3–8.5, Laravel 10–13, and Milvus 2.5.21, 2.6.21, and 3.0.0.

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
- Correct outdated README request shapes and named arguments.
- Keep ordinary Pest 2–4 test runs independent of an installed code-coverage driver.

[0.2.0]: https://github.com/HelgeSverre/milvus/compare/v0.1.0...v0.2.0
