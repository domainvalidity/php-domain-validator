<?php

use DomainValidity\Parse\HostParser;

describe('HostParser scheme handling', function () {
    it('accepts http URLs', function () {
        expect(HostParser::parse('http://example.com/path')['host'])->toBe('example.com');
    });

    it('accepts https URLs', function () {
        expect(HostParser::parse('https://example.com')['host'])->toBe('example.com');
    });

    it('accepts uppercase http/https schemes', function () {
        expect(HostParser::parse('HTTPS://example.com')['host'])->toBe('example.com');
    });

    it('throws on ftp scheme instead of mis-parsing the host', function () {
        expect(fn () => HostParser::parse('ftp://example.com'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on file scheme', function () {
        expect(fn () => HostParser::parse('file:///etc/passwd'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws on custom scheme', function () {
        expect(fn () => HostParser::parse('myapp+v1://example.com'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('HostParser edge-case inputs', function () {
    it('throws on empty string', function () {
        expect(fn () => HostParser::parse(''))->toThrow(InvalidArgumentException::class);
    });

    it('throws on javascript pseudo-URL', function () {
        expect(fn () => HostParser::parse('javascript:alert(1)'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('extracts host when a port is present', function () {
        $parts = HostParser::parse('example.com:8080/path');

        expect($parts['host'])->toBe('example.com')
            ->and($parts['port'])->toBe(8080);
    });

    it('extracts host when userinfo is present', function () {
        expect(HostParser::parse('user:secret@example.com')['host'])->toBe('example.com');
    });
});

describe('Validator edge-case inputs', function () {
    it('marks an IPv4 address as invalid', function () {
        expect(getInstance()->validate('1.2.3.4')->isValid())->toBeFalse();
    });

    it('marks an IPv6 literal as invalid', function () {
        expect(getInstance()->validate('http://[::1]/')->isValid())->toBeFalse();
    });

    it('marks a host with a trailing dot as invalid', function () {
        expect(getInstance()->validate('example.com.')->isValid())->toBeFalse();
    });
});
