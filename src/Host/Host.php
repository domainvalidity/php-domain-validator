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
        public ?bool $isPrivate = null,
    ) {
        $parsed = HostParser::parse($this->original);

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

        $root = str_replace(strval($this->tld), '', strval($this->host));
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
        return !empty($this->tld());
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
            'valid' => !empty($this->tld()),
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
