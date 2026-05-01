# Security Policy

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY, [SEE BELOW](#reporting-a-vulnerability).**

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 3.0.1+  | :white_check_mark: |
| 3.0.0   | :x: (deprecated — see below) |
| 2.x     | :white_check_mark: |
| 1.x     | :x: |

## Known insecure versions

- **v3.0.0** — contained two defects in the host/TLD parsing path:
  a regex-construction issue in `Host::tld()` that escaped only `.`
  characters, and a substring-match scheme detection in `HostParser`
  that mis-classified URLs containing `http://` later in their
  path/query. Both are fixed in **v3.0.1**. We recommend upgrading
  immediately. No exploit details are published; see `CHANGELOG.md`
  for the high-level description.

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please send an email to Alejandro Morelos at info@domainvalidity.dev. All security vulnerabilities will be promptly addressed.