# Doma(in)Validity — Usage Guide

A complete walkthrough of how to install, configure, and use
`domainvalidity/php-domain-validator` (v3.0.x).

---

## Contents

- [Installation](#installation)
- [Quick start](#quick-start)
- [Working with the Public Suffix List](#working-with-the-public-suffix-list)
  - [Fetching it securely](#fetching-it-securely)
  - [Caching strategy](#caching-strategy)
- [API reference](#api-reference)
  - [`Factory`](#factory)
  - [`Validator`](#validator)
  - [`Host`](#host)
- [Worked examples](#worked-examples)
- [Error handling](#error-handling)
- [Security notes](#security-notes)

---

## Installation

Requires **PHP 8.2 or newer** (v3.x). Install via Composer:

```bash
composer require domainvalidity/php-domain-validator
```

The package has zero runtime dependencies.

---

## Quick start

```php
use DomainValidity\Factory;

// Pass the raw contents of the Public Suffix List (PSL).
$psl       = file_get_contents(__DIR__ . '/storage/public_suffix_list.dat');
$validator = Factory::make($psl);

$host = $validator->validate('https://www.example.co.uk/path?x=1');

$host->isValid();   // bool — true if a known TLD was matched
$host->tld();       // 'co.uk'
$host->domain();    // 'example.co.uk'
$host->toString();  // 'www.example.co.uk'
$host->isPrivate(); // false (true for entries in the PSL "PRIVATE DOMAINS" section)
```

`validate()` accepts plain hostnames *or* full URLs. The scheme,
userinfo, port, path, query, and fragment are all stripped — only the
host portion is validated. Only `http://` and `https://` schemes are
accepted; any other explicit scheme (`ftp://`, `file://`, …) throws an
`\InvalidArgumentException`.

Wildcard (`*.ck`) and exception (`!www.ck`) rules from the PSL are
fully supported: `foo.bar.ck` resolves the suffix `bar.ck`, while
`www.ck` resolves `ck` because the exception rule prevails.

---

## Working with the Public Suffix List

This package ships **no PSL data of its own** at runtime. You provide
the raw `public_suffix_list.dat` contents to `Factory::make()`. The
canonical source is <https://publicsuffix.org/list/public_suffix_list.dat>.

### Fetching it securely

The README shows a one-liner for brevity; in production you should
fetch the PSL through a properly-configured HTTPS context, with
timeouts, error handling, and an explicit cache:

```php
$cachePath = __DIR__ . '/storage/public_suffix_list.dat';
$cacheAge  = is_file($cachePath) ? time() - filemtime($cachePath) : PHP_INT_MAX;

if ($cacheAge > 86_400) {
    $context = stream_context_create([
        'http' => [
            'timeout'       => 10,
            'follow_location' => 1,
            'header'        => "User-Agent: my-app/1.0\r\n",
        ],
        'ssl'  => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ]);

    $contents = @file_get_contents(
        'https://publicsuffix.org/list/public_suffix_list.dat',
        false,
        $context
    );

    if ($contents !== false) {
        file_put_contents($cachePath, $contents, LOCK_EX);
    }
}

$psl = file_get_contents($cachePath);
if ($psl === false || $psl === '') {
    throw new RuntimeException('Public Suffix List unavailable.');
}

$validator = \DomainValidity\Factory::make($psl);
```

For long-running services, prefer a real HTTP client (Guzzle, Symfony
HttpClient) and a job/cron that refreshes the PSL daily.

### Caching strategy

The PSL is updated a few times per week, so refreshing it more than
once per day is wasteful. Two practical patterns:

1. **Filesystem cache** (above) — refresh on read, gated by file
   `mtime`. Good for small apps and CLI tools.
2. **Job-driven cache** — a daily cron writes the PSL to disk or a
   shared store; application processes read from there only. Good for
   web apps where you don't want a request to ever block on an external
   download.

You can also keep the parsed `Validator` in memory (e.g. a singleton)
to avoid re-parsing the PSL on every request:

```php
final class ValidatorRegistry
{
    private static ?\DomainValidity\Validator $instance = null;

    public static function get(): \DomainValidity\Validator
    {
        return self::$instance ??= \DomainValidity\Factory::make(
            (string) file_get_contents(__DIR__ . '/storage/public_suffix_list.dat')
        );
    }
}
```

---

## API reference

### `Factory`

```php
namespace DomainValidity;

final class Factory
{
    public static function make(string $dotDatContent): Validator;
}
```

- **`$dotDatContent`** — the raw bytes of `public_suffix_list.dat`.
- **Returns** — a configured `Validator`.
- **Throws** — nothing directly. Malformed PSL content yields a
  `Validator` whose internal lookup tables are empty; `validate()` will
  return `Host` instances flagged invalid.

### `Validator`

```php
namespace DomainValidity;

class Validator
{
    public function validate(string $host): \DomainValidity\Host\Host;
}
```

- **`$host`** — a hostname (`example.com`), a host with port, or a full
  URL with scheme/path/query.
- **Returns** — a `Host` instance describing the parsed input.
- **Throws** — `\InvalidArgumentException` if the input cannot be parsed
  as a URL/host (e.g. an empty string or `http://`), or if it carries a
  scheme other than `http`/`https`.

### `Host`

```php
namespace DomainValidity\Host;

class Host
{
    public string  $original;     // exactly what was passed in
    public ?string $host;         // extracted host portion (lowercase ASCII)
    public ?string $domain;       // root + TLD (e.g. example.co.uk)
    public ?string $tld;          // matched public suffix (e.g. co.uk)
    public ?bool   $isPrivate;    // true for PSL "PRIVATE DOMAINS" matches

    public function original(?string $value = null): string|self;
    public function tld(?string $value = null):      string|self;
    public function domain(?string $value = null):   string|self;
    public function isValid(): bool;
    public function isPrivate(?bool $value = null):  bool|self;
    public function toString(): string;       // alias of (string) $host
    public function toArray():  array;        // ['valid','original','host','domain','tld','private']
    public function __toString(): string;
}
```

The accessor/setter methods follow a fluent pattern: call with no
argument to read, call with an argument to mutate and chain.

`isValid()` returns `true` if a TLD from the PSL was matched **and**
the remaining root passes a basic charset check
(`/^[a-zA-Z0-9.-]+$/`).

**IDN note:** the root charset check is ASCII-only. Unicode public
suffixes (e.g. `嘉里大酒店`) are matched, but a Unicode *root* label
(e.g. `例え.jp`) is flagged invalid. For internationalized roots,
convert the host to punycode first (`idn_to_ascii()`, requires the
`intl` extension) before calling `validate()`. Lowercasing of the
input is byte-wise (`strtolower`), which leaves multi-byte UTF-8
labels untouched — uppercase Unicode input is not normalized.

`isPrivate()` is `true` for hosts that fall under a private suffix
(e.g. `*.amazonaws.com`, `*.github.io`).

---

## Worked examples

### Standard domain

```php
$h = $v->validate('https://www.example.com/');
$h->tld();       // 'com'
$h->domain();    // 'example.com'
$h->toString();  // 'www.example.com'
$h->isValid();   // true
```

### Multi-level TLD

```php
$h = $v->validate('https://www.example.co.uk/');
$h->tld();    // 'co.uk'
$h->domain(); // 'example.co.uk'
```

### Private suffix

```php
$h = $v->validate('d-abc123.execute-api.us-west-1.amazonaws.com');
$h->tld();       // 'com'
$h->domain();    // 'amazonaws.com'
$h->isPrivate(); // true
```

### Invalid TLD

```php
$h = $v->validate('https://adro.is.a.rocker.and/');
$h->isValid(); // false
$h->tld();     // ''
$h->domain();  // ''
```

### URL with query/path containing `http://`

```php
$h = $v->validate('evil.example.com/redirect?to=http://attacker.example');
$h->toString(); // 'evil.example.com'
$h->domain();   // 'example.com'
```

---

## Error handling

`validate()` follows a "return a result object, throw only on
unparseable input" model:

| Input shape                              | Behavior                                             |
| ---------------------------------------- | ---------------------------------------------------- |
| Valid hostname or URL                    | Returns `Host` with `isValid()` reflecting the TLD match |
| Hostname with no matching public suffix  | Returns `Host` with `isValid() === false`            |
| Empty string, `http://`, malformed input | Throws `\InvalidArgumentException` (from `HostParser`) |

Defensive callers should wrap the call:

```php
try {
    $host = $validator->validate($userInput);
} catch (\InvalidArgumentException $e) {
    // unparseable input — log, reject, etc.
    return null;
}

if (!$host->isValid()) {
    // parsed but no public suffix matched
    return null;
}
```

---

## Security notes

- The validator does **not** perform DNS resolution. A `valid` result
  means the hostname is well-formed and uses a known public suffix —
  not that the domain is registered, reachable, or trustworthy.
- Always refresh the Public Suffix List (we recommend daily). Stale
  PSL data leads to false negatives (new TLDs not recognized) and
  false positives (deprecated entries treated as live). The bundled
  `data/public_suffix_list.dat` is used **only by the test suite**;
  contributors can refresh it with `composer psl:update`.
- See [`SECURITY.md`](../SECURITY.md) for the package's supported
  versions and how to report vulnerabilities responsibly.
