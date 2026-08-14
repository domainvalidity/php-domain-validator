<?php

namespace DomainValidity;

use DomainValidity\Host\Host;
use DomainValidity\Parse\PublicSuffixListParser;

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

        $parts = explode('.', $host->toString());

        $tld = $this->findPublicSuffix($parts, $this->publicSuffixList['icann'] ?? []);

        if ($tld !== null) {
            $host->isPrivate(
                $this->checkIfIsPrivate($parts)
            );

            $host->tld($tld);
        }

        return $host;
    }

    /**
     * Resolve the public suffix for the given host parts using the
     * Public Suffix List algorithm: among all matching rules the
     * exception rule prevails if present, otherwise the longest rule;
     * an exception rule's suffix is the rule minus its leftmost label.
     *
     * @param array<string> $parts Domain parts (e.g., ['www', 'adro', 'com', 'mx'])
     * @param array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $section
     */
    protected function findPublicSuffix(array $parts, array $section): ?string
    {
        if (empty($parts)) {
            return null;
        }

        $reversed = array_reverse($parts);

        $match = $this->findPrevailingRule($reversed, $section);

        if ($match === null) {
            return null;
        }

        $depth = $match['exception'] ? $match['depth'] - 1 : $match['depth'];

        if ($depth < 1) {
            return null;
        }

        return implode('.', array_reverse(array_slice($reversed, 0, $depth)));
    }

    /**
     * Find the prevailing rule for the host in a hierarchical section.
     * Explores both the literal label and the `*` wildcard at each level,
     * since the Public Suffix List matches every rule independently.
     *
     * @param array<string> $reversed Host labels, rightmost first
     * @param array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $section
     * @return array{depth: int, exception: bool}|null
     */
    protected function findPrevailingRule(array $reversed, array $section, int $index = 0): ?array
    {
        if (!isset($reversed[$index]) || $reversed[$index] === '') {
            return null;
        }

        $best = null;

        $keys = $reversed[$index] === '*' ? ['*'] : [$reversed[$index], '*'];

        foreach ($keys as $key) {
            if (!isset($section[$key]) || !is_array($section[$key])) {
                continue;
            }

            $child = $section[$key];
            $candidate = null;

            if (isset($child[PublicSuffixListParser::RULE_EXCEPTION])) {
                $candidate = ['depth' => $index + 1, 'exception' => true];
            } elseif (isset($child[PublicSuffixListParser::RULE_END])) {
                $candidate = ['depth' => $index + 1, 'exception' => false];
            }

            $deeper = $this->findPrevailingRule($reversed, $child, $index + 1);

            $best = $this->prevailing($best, $this->prevailing($candidate, $deeper));
        }

        return $best;
    }

    /**
     * Pick the prevailing rule between two candidates: an exception rule
     * beats any non-exception rule; otherwise the longer rule wins.
     *
     * @param array{depth: int, exception: bool}|null $a
     * @param array{depth: int, exception: bool}|null $b
     * @return array{depth: int, exception: bool}|null
     */
    protected function prevailing(?array $a, ?array $b): ?array
    {
        if ($a === null) {
            return $b;
        }

        if ($b === null) {
            return $a;
        }

        if ($a['exception'] !== $b['exception']) {
            return $a['exception'] ? $a : $b;
        }

        return $b['depth'] > $a['depth'] ? $b : $a;
    }

    /**
     * Check if host matches a rule in the private domains section.
     * A matching `!` exception rule cancels the private classification.
     *
     * @param array<string> $parts Domain parts
     */
    protected function checkIfIsPrivate(array $parts): bool
    {
        /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $section */
        $section = $this->publicSuffixList['private'] ?? [];

        $match = $this->findPrevailingRule(array_reverse($parts), $section);

        return $match !== null && !$match['exception'];
    }
}
