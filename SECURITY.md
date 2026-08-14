# Security Policy

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY, [SEE BELOW](#reporting-a-vulnerability).**

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.0.4+  | :white_check_mark: |
| 2.0.0 – 2.0.3 | :x: (see below) |
| 1.x     | :x: |

## Known insecure versions

- **v2.0.0 – v2.0.3** — contained the parsing defects fixed in v3.0.1
  and v3.1.0, backported to **v2.0.4**: a regex-construction issue in
  `Host::tld()` (only `.` was escaped), substring-based scheme
  detection in `HostParser` (URLs carrying `http://` in their
  path/query were mis-parsed), silently mis-parsed non-http schemes
  (`ftp://example.com` yielded the host `ftp`), ignored Public Suffix
  List wildcard (`*`) and exception (`!`) rules, and a
  substring-based private-domain check that could mark unrelated
  hosts as private. Upgrade to **v2.0.4** (or v3.x) immediately.

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please send an email to Alejandro Morelos at info@domainvalidity.dev. All security vulnerabilities will be promptly addressed.