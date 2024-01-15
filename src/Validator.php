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

        $tld = $this->getTld(explode('.', strval($host->toString())), 'icann');

        if ($tld !== null) {
            $host->isPrivate(
                $this->checkIfIsPrivate($host->toString())
            );
        }

        $host->tld($tld);

        return $host;
    }

    /**
     * @param array<string> $parts
     */
    protected function getTld(array $parts, string $section, bool $partialFound = false, ?string $tld = null): ?string
    {
        $current = end($parts) . ($tld ? ".{$tld}" : '');
        unset($parts[count($parts) - 1]);

        foreach ($this->publicSuffixList[$section] as $item) {
            if ($current === $item) {
                return $this->getTld(
                    parts: $parts,
                    section: $section,
                    partialFound: true,
                    tld: $current,
                );
            }
        }

        return $partialFound ? strval($tld) : null;
    }

    protected function checkIfIsPrivate(string $host): bool
    {
        foreach ($this->publicSuffixList['private'] as $item) {
            if (strpos($host, trim($item, '*')) !== false) {
                return true;
            }
        }

        return false;
    }
}
