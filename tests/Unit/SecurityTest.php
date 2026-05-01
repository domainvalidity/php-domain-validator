<?php

use DomainValidity\Host\Host;

describe('Host::tld() with regex metacharacters does not crash', function () {
    $validator = getInstance();
    $host = $validator->validate('example.com');

    it('accepts a TLD containing PCRE metacharacters without raising', function () use ($host) {
        $result = $host->tld('(*+?[');

        expect($result)->toBeInstanceOf(Host::class);
    });

    it('still allows downstream inspection after the unusual setter call', function () use ($host) {
        $host->tld('(*+?[');

        expect($host->toString())->toBe('example.com');
    });
});

describe('HostParser scheme detection only triggers on a leading scheme', function () {
    $validator = getInstance();
    $url = 'evil.example.com/path?redirect=http://attacker.example';
    $host = $validator->validate($url);

    it('extracts the host as evil.example.com regardless of `http://` later in the URL', function () use ($host) {
        expect($host->toString())->toBe('evil.example.com');
    });

    it('still resolves a com TLD for the leading host', function () use ($host) {
        expect($host->tld())->toBe('com');
    });

    it('still resolves the domain as example.com', function () use ($host) {
        expect($host->domain())->toBe('example.com');
    });
});

describe('HostParser still rejects unparseable input', function () {
    $validator = getInstance();

    it('throws InvalidArgumentException when the input has no extractable host', function () use ($validator) {
        expect(fn () => $validator->validate('http://'))->toThrow(InvalidArgumentException::class);
    });
});

describe('Regression: substring TLD match still resolves the right root', function () {
    $validator = getInstance();
    $host = $validator->validate('a.b.c.compass.com');

    it('keeps compass.com as the domain after the regex-to-string-op refactor', function () use ($host) {
        expect($host->domain())->toBe('compass.com');
    });
});
