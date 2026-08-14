<?php

declare(strict_types=1);

namespace DomainValidity\Parse;

use InvalidArgumentException;

class HostParser
{
    /**
     * Matches any RFC 3986 scheme followed by '://'.
     */
    private const SCHEME_PATTERN = '#^([a-z][a-z0-9+.-]*)://#i';

    /**
     * @return array<string, int<0, 65535>|string|null>
     */
    public static function parse(string $host): array
    {
        $isSchemePresent = preg_match(self::SCHEME_PATTERN, $host, $matches) === 1;

        if ($isSchemePresent) {
            $scheme = strtolower($matches[1]);

            if ($scheme !== 'http' && $scheme !== 'https') {
                throw new InvalidArgumentException(
                    "Unsupported scheme '{$scheme}': only http and https are accepted"
                );
            }
        } else {
            $host = 'http://' . $host;
        }

        $parts = parse_url($host);

        if (!is_array($parts) || !isset($parts['host'])) {
            throw new InvalidArgumentException("Invalid host");
        }

        $parts['scheme'] = $isSchemePresent && isset($parts['scheme']) ? $parts['scheme'] : null;

        return $parts;
    }
}
