<?php

/**
 * Backwards-compatibility shims for the pre-3.1 global helper functions.
 * Canonical implementations live in DomainValidity\Support (functions.php).
 *
 * Guarded with function_exists so a consumer app defining same-named
 * globals no longer triggers a fatal redeclaration error.
 */

declare(strict_types=1);

if (!function_exists('remove_comments')) {
    /**
     * @deprecated 3.1.0 Use DomainValidity\Support\remove_comments()
     */
    function remove_comments(string $text): ?string
    {
        return \DomainValidity\Support\remove_comments($text);
    }
}

if (!function_exists('remove_empty_lines')) {
    /**
     * @deprecated 3.1.0 Use DomainValidity\Support\remove_empty_lines()
     */
    function remove_empty_lines(string $text): ?string
    {
        return \DomainValidity\Support\remove_empty_lines($text);
    }
}

if (!function_exists('validate_domain_root')) {
    /**
     * @deprecated 3.1.0 Use DomainValidity\Support\validate_domain_root()
     */
    function validate_domain_root(string $root): bool
    {
        return \DomainValidity\Support\validate_domain_root($root);
    }
}
