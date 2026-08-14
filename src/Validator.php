<?php

namespace DomainValidity;

use DomainValidity\Host\Host;

class Validator
{
    /**
     * @param array<string, array<int, string>> $publicSuffixList
     */
    public function __construct(
        protected array $publicSuffixList,
    ) {
    }

    public function validate(string $host): Host
    {
        $host = new Host($host);

        $tld = $this->findPublicSuffix(
            explode('.', strval($host->toString())),
            $this->publicSuffixList['icann'] ?? []
        );

        if ($tld !== null) {
            $host->isPrivate(
                $this->checkIfIsPrivate($host->toString())
            );

            $host->tld($tld);
        }

        return $host;
    }

    /**
     * Resolve the public suffix for the given host parts using the
     * Public Suffix List algorithm: among all matching rules the
     * exception (`!`) rule prevails if present, otherwise the longest
     * rule; wildcard (`*`) labels match any single label; an exception
     * rule's suffix is the rule minus its leftmost label.
     *
     * @param array<string> $parts Host labels (e.g., ['www', 'adro', 'com', 'mx'])
     * @param array<int, string> $rules Rule lines from one PSL section
     */
    protected function findPublicSuffix(array $parts, array $rules): ?string
    {
        $match = $this->findPrevailingRule($parts, $rules);

        if ($match === null) {
            return null;
        }

        $depth = $match['exception'] ? $match['depth'] - 1 : $match['depth'];

        if ($depth < 1) {
            return null;
        }

        return implode('.', array_slice($parts, -$depth));
    }

    /**
     * Find the prevailing rule for the host among the section's rules.
     *
     * @param array<string> $parts Host labels
     * @param array<int, string> $rules Rule lines from one PSL section
     * @return array{depth: int, exception: bool}|null
     */
    protected function findPrevailingRule(array $parts, array $rules): ?array
    {
        if ($parts === [] || in_array('', $parts, true)) {
            return null;
        }

        $best = null;

        foreach ($rules as $rule) {
            $rule = trim($rule);

            if ($rule === '' || str_starts_with($rule, '//')) {
                continue;
            }

            $exception = str_starts_with($rule, '!');
            if ($exception) {
                $rule = substr($rule, 1);
            }

            $ruleParts = explode('.', $rule);
            $depth = count($ruleParts);

            if ($rule === '' || $depth > count($parts)) {
                continue;
            }

            if (!$this->ruleMatches($ruleParts, array_slice($parts, -$depth))) {
                continue;
            }

            if (
                $best === null
                || ($exception && !$best['exception'])
                || ($exception === $best['exception'] && $depth > $best['depth'])
            ) {
                $best = ['depth' => $depth, 'exception' => $exception];
            }
        }

        return $best;
    }

    /**
     * @param array<string> $ruleParts
     * @param array<string> $hostParts Same length as $ruleParts
     */
    protected function ruleMatches(array $ruleParts, array $hostParts): bool
    {
        foreach ($ruleParts as $index => $label) {
            if ($label !== '*' && $label !== $hostParts[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if host matches a rule in the private domains list.
     * A matching `!` exception rule cancels the private classification.
     */
    protected function checkIfIsPrivate(string $host): bool
    {
        $match = $this->findPrevailingRule(
            explode('.', $host),
            $this->publicSuffixList['private'] ?? []
        );

        return $match !== null && !$match['exception'];
    }
}
