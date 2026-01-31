<p align="center">
    <img src="./.art/domainvalidity.png" width="200">
</p>

# Doma(in)Validity PHP package.

Light PHP package to validate domains.

[Doma(in)Validity](https://api.domainvalidity.dev/) was born because I found myself searching online about how to check if a domain was valid. I always ended up using regular expressions that were too complex to account for several scenarios (mainly the TLD having different formats), it was just a pain in the butt because I always had to go back to that code to fix the regex to account for an edge case that I didn't think about.

### Requirements

- PHP >= 8.2.0 (for v3.x)
- PHP >= 8.1.0 (for v2.x)

## Installation

You can install the package via composer:

```bash
composer require domainvalidity/php-domain-validator
```

## Usage

```php
use DomainValidity\Factory;

$contents = file_get_contents('https://publicsuffix.org/list/public_suffix_list.dat');

$validator = Factory::make($contents);

$host = $validator->validate('www.domainvalidity.dev');
```

> **Note:** You should cache the contents of the public suffix list and download them no more than once per day, as it is not updated more than a few times per week; more frequent downloading is pointless.