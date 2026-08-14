# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.0] - 2026-08-14

### Security / Fixed

- **Public Suffix List wildcard (`*`) and exception (`!`) rules are now
  fully supported** in the ICANN section (the private section gains `!`
  support too). Previously `findTldInHierarchy` only did exact-label
  lookups, so the PSL's 200+ wildcard rules (`*.ck`,
  `*.compute.amazonaws.com`, …) and 8 exception rules (`!www.ck`, …)
  were ignored and consumers could derive a wrong registrable domain —
  a hazard for cookie scoping and host allowlisting.
- **Non-http schemes now throw** `InvalidArgumentException` instead of
  mis-parsing. `ftp://example.com` used to be prefixed to
  `http://ftp://example.com` and yielded the host `ftp`.
- **UTF-8 rule lines are no longer corrupted while parsing the PSL.**
  Section content is now split on `\r\n`/`\r`/`\n` explicitly; the
  previous logic could split inside multi-byte labels containing a
  `0x85` byte (e.g. `嘉里大酒店`).
- The internal end-of-rule marker now contains a NUL byte, so a
  crafted PSL line can no longer collide with it; rule lines containing
  NUL bytes are discarded.

### Changed (potentially breaking for edge cases)

- The helper functions `remove_comments()`, `remove_empty_lines()` and
  `validate_domain_root()` moved to `DomainValidity\Support\`. The old
  global names remain available as deprecated `function_exists`-guarded
  shims (`src/functions_global.php`), so existing callers keep working
  and the fatal redeclaration risk when a consumer app defines
  same-named globals is gone. The shims will be removed in v4.0.
- `Factory` is now `final` (matching the documented API).
- The `InvalidArgumentException` thrown by `HostParser` no longer
  carries the misleading exception code `500`.

### Added

- `composer psl:update` script to refresh the bundled test-only PSL
  snapshot from publicsuffix.org.
- Dependabot configuration for Composer and GitHub Actions.
- CI now runs on pushes to the `3.x` branch; `actions/checkout` bumped
  to v4; PHPCS now also lints `tests/`.
- Test coverage for the PSL parser, helper functions, wildcard and
  exception rules, and parser edge cases (ports, userinfo, IP
  literals, trailing dots, empty input).

## [3.0.1] - 2026-05-01

### Security

- `Host::tld()` no longer builds a PCRE pattern from a publicly-settable
  value. The previous implementation only escaped `.` characters, so any
  other PCRE metacharacter in the supplied TLD (e.g. `* + ? | ^ $ ( )
  [ ] { } \`) was inserted into a dynamic regex unescaped, allowing
  unintended match behavior, PCRE compile errors, and a small ReDoS
  surface. The suffix is now removed with `str_ends_with` + `substr` —
  no regex, nothing to escape.
- `HostParser` now uses `str_starts_with` for scheme detection. The
  previous `strpos(...) !== false` check matched the literals
  `http://` / `https://` anywhere in the input, so URLs containing
  those substrings in their path or query (e.g.
  `evil.example.com/?u=http://x`) were mis-classified as
  already-schemed and skipped the `http://` prefix needed by
  `parse_url`.

### Deprecated

- **v3.0.0 is deprecated** due to the issues above. All consumers should
  upgrade to **v3.0.1**. See `SECURITY.md` for the supported-versions
  matrix.

### Docs

- New `docs/USAGE.md` — full usage guide, secure PSL fetch pattern,
  caching strategy, complete API reference, worked examples, error
  handling.
- README slimmed down to a landing page that links to the usage guide,
  security policy, and changelog.

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
