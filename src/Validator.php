<?php

namespace DomainValidity;

use DomainValidity\Host\Host;

class Validator
{
    /**
     * @param array<'icann'|'private'|string,array<string,true|array<string,true|array<string,
     *     true|array<string,true|array>>>>> $publicSuffixList
     * @phpstan-ignore-next-line
     */
    public function __construct(
        protected array $publicSuffixList,
    ) {
    }

    public function validate(string $host): Host
    {
        $host = new Host($host);

        $tld = $this->findTldInHierarchy(
            explode('.', (string) $host->toString()),
            $this->publicSuffixList['icann']
        );

        if ($tld !== null) {
            $host->isPrivate(
                $this->checkIfIsPrivate($host->toString())
            );
        }

        $host->tld($tld);

        return $host;
    }

    /**
     * Find TLD in hierarchical structure using iterative lookup.
     * Traverses the hierarchy from right to left, checking each suffix.
     * Returns the longest matching domain suffix.
     *
     * @param array<string> $parts Domain parts (e.g., ['www', 'adro', 'com', 'mx'])
     * @param array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $section
     *              The hierarchical section
     */
    protected function findTldInHierarchy(array $parts, array $section): ?string
    {
        if (empty($parts)) {
            return null;
        }

        $longestMatch = null;
        $reversed = array_reverse($parts);
        $current = &$section;
        $depth = 0;

        // Traverse from rightmost (top-level domain) leftward
        foreach ($reversed as $index => $part) {
            if (!isset($current[$part])) {
                // No match at this level, stop traversing
                break;
            }

            $current = &$current[$part];
            $depth++;

            // If this level is marked as a complete domain, record it
            if (isset($current['__end__'])) {
                // Build the matched suffix by taking the rightmost 'depth' parts in original order
                $suffix_parts = array_slice($reversed, 0, $depth);
                $longestMatch = implode('.', array_reverse($suffix_parts));
            }
        }

        return $longestMatch;
    }

    /**
     * Check if host is in private domains list.
     * Uses hierarchical lookup similar to getTld with wildcard support.
     */
    protected function checkIfIsPrivate(string $host): bool
    {
        $parts = explode('.', $host);
        $reversed = array_reverse($parts);
        /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $section */
        $section = $this->publicSuffixList['private'];

        $current = &$section;

        // Traverse hierarchy from rightmost part
        foreach ($reversed as $part) {
            // Check for exact match
            if (isset($current[$part])) {
                if (isset($current[$part]['__end__'])) {
                    return true;
                }
                $current = &$current[$part];
                continue;
            }

            // Check for wildcard match
            if (isset($current['*'])) {
                if (isset($current['*']['__end__'])) {
                    return true;
                }
                $current = &$current['*'];
                continue;
            }

            // No match found at this level
            return false;
        }

        return false;
    }
}
