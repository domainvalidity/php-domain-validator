<?php

namespace DomainValidity\Parse;

use InvalidArgumentException;

class HostParser
{
    /**
     * @return array<string, int<0, 65535>|string|null>
     */
    public static function parse(string $host): array
    {
        $isSchemePresent = strpos($host, 'https://') !== false ||
            strpos($host, 'http://') !== false;

        if (!$isSchemePresent) {
            $host = 'http://' . $host;
        }

        $parts = parse_url($host);

        $fn = function ($domainName) {
            return boolval(
                preg_match(
                    "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i",
                    $domainName
                )
            );
        };

        if (!is_array($parts) || !isset($parts['host']) || !$fn($parts['host'])) {
            throw new InvalidArgumentException("Invalid host", 500);
        }

        $parts['scheme'] = $isSchemePresent && isset($parts['scheme']) ? $parts['scheme'] : null;

        return $parts;
    }
}
