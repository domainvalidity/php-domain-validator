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
- **v3.0.x (hardening in v3.1.0)** — Public Suffix List wildcard (`*`)
  and exception (`!`) rules were ignored during suffix resolution, so
  hosts under wildcard suffixes (e.g. `*.ck`) could resolve a wrong
  registrable domain — relevant if you use the result for cookie
  scoping or host allowlisting. Non-http schemes (`ftp://…`) were also
  silently mis-parsed instead of rejected. Fixed in **v3.1.0**.

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please send an email to Alejandro Morelos at info@domainvalidity.dev. All security vulnerabilities will be promptly addressed.