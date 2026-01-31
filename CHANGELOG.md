# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2026-01-31

### Breaking Changes

- **Minimum PHP version is now 8.2.0** (was 8.1.0)
  - For PHP 8.1 support, use the v2.x branch

### Added

- Hierarchical suffix list structure for dramatically improved performance
- Precise recursive type definitions throughout codebase
- Enhanced type safety with strict PHPStan analysis

### Changed

- ~100-200x faster domain validation through hash-based O(1) lookups
- Refactored internal suffix parser with hierarchical maps
- Updated to Pest 3.x and PHPUnit 11.x for modern testing

### Dependencies

- `php`: ^8.1.0 -> ^8.2.0
- `pestphp/pest`: ^2.0 -> ^3.0
- `phpunit/phpunit`: ^10.5 -> ^11.5

## [2.0.3] - 2024-03-08

### Changed

- Updated public suffix list

## [2.0.2] - 2024-02-29

### Fixed

- Domain parsing when TLD substring is contained in the domain name

## [2.0.1] - 2024-01-24

### Changed

- Updated public suffix list

## [2.0.0] - 2024-01-15

### Changed

- Major version bump with improved validation logic

## [1.0.3] - 2024-01-15

### Changed

- Deprecated in favor of v2.x

## [1.0.2] - 2023-07-27

### Fixed

- Minor bug fixes

## [1.0.1] - 2023-07-27

### Fixed

- Minor bug fixes

## [1.0.0] - 2023-07-27

### Added

- Initial stable release
- Domain validation functionality
- Public suffix list support
- Private domain detection

## [0.1.0] - 2023-07-26

### Added

- Initial development release
