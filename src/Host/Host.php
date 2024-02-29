<?php

namespace DomainValidity\Host;

use DomainValidity\Parse\HostParser;

class Host
{
    public function __construct(
        public string $original,
        public ?string $host = null,
        public ?string $domain = null,
        public ?string $tld = null,
        private ?bool $isValid = null,
        public ?bool $isPrivate = null,
    ) {
        $parsed = HostParser::parse(strtolower($this->original));

        $this->host = strval($parsed['host']);
    }

    public function original(?string $original = null): string|self
    {
        if ($original === null) {
            return $this->original;
        }

        $this->original = $original;

        return $this;
    }

    public function tld(?string $tld = null): string|self
    {
        if ($tld === null) {
            return $this->tld ?? '';
        }

        $this->tld = $tld;

        $escaped = str_replace('.', '\.', '.' . strval($this->host));

        /** @var string $root */
        $root = preg_replace("/$escaped$/", '', strval($this->host));

        if (!validate_domain_root(trim($root, '.'))) {
            $this->isValid = false;
        }

        $root = explode('.', trim(strval($root), '.'));

        $this->domain(end($root) . '.' . $this->tld);

        return $this;
    }

    public function domain(?string $domain = null): string|self
    {
        if ($domain === null) {
            return $this->domain ?? '';
        }

        $this->domain = $domain;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->isValid ?? !empty($this->tld());
    }

    public function isPrivate(?bool $isPrivate = null): bool|self
    {
        if ($isPrivate === null) {
            return $this->isPrivate ?? false;
        }

        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function toString(): string
    {
        return strval($this->host);
    }

    /**
     * @return array<string, bool|string>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'original' => $this->original(),
            'host' => $this->toString(),
            'domain' => $this->domain(),
            'tld' => $this->tld(),
            'private' => $this->isPrivate(),
        ];
    }

    public function __toString()
    {
        return $this->toString();
    }
}
