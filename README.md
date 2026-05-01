<p align="center">
    <img src="./.art/domainvalidity.png" width="200">
</p>

# Doma(in)Validity PHP package

Light PHP package to validate domains using the Mozilla
[Public Suffix List](https://publicsuffix.org/).

[Doma(in)Validity](https://api.domainvalidity.dev/) was born because the
usual "validate a domain" regex grows new edge cases every time you
look away — multi-level TLDs (`co.uk`, `com.mx`), IDN labels, private
suffixes (`*.amazonaws.com`). This package outsources the hard part to
the PSL.

## Requirements

- PHP **>= 8.2.0** (for v3.x)
- PHP **>= 8.1.0** (for v2.x)

## Installation

```bash
composer require domainvalidity/php-domain-validator
```

## Quick start

```php
use DomainValidity\Factory;

$psl       = file_get_contents('path/to/public_suffix_list.dat');
$validator = Factory::make($psl);

$host = $validator->validate('www.domainvalidity.dev');

$host->isValid();   // true
$host->tld();       // 'dev'
$host->domain();    // 'domainvalidity.dev'
$host->toString();  // 'www.domainvalidity.dev'
```

> Cache the Public Suffix List and refresh at most once per day. It is
> updated only a few times per week, so more frequent fetching is
> wasteful. See the usage guide below for a secure, cached fetch
> pattern.

## Documentation

- **[`docs/USAGE.md`](docs/USAGE.md)** — full usage guide, secure PSL
  fetching, caching strategy, complete API reference, worked examples,
  and error-handling model.
- **[`SECURITY.md`](SECURITY.md)** — supported versions and how to
  report vulnerabilities.
- **[`CHANGELOG.md`](CHANGELOG.md)** — release history.

## License

MIT — see [`LICENSE`](LICENSE).
