<?php

describe('Host value object', function () {
    $host = getInstance()->validate('https://www.adro.com.mx/blog');

    it('casts to the parsed host string', function () use ($host) {
        expect((string) $host)->toBe('www.adro.com.mx');
    });

    it('exports the full result with toArray', function () use ($host) {
        expect($host->toArray())->toBe([
            'valid' => true,
            'original' => 'https://www.adro.com.mx/blog',
            'host' => 'www.adro.com.mx',
            'domain' => 'adro.com.mx',
            'tld' => 'com.mx',
            'private' => false,
        ]);
    });

    it('leaves domain and tld empty for an unmatched host', function () {
        $invalid = getInstance()->validate('foo.invalid-tld-zzz');

        expect($invalid->toArray())->toBe([
            'valid' => false,
            'original' => 'foo.invalid-tld-zzz',
            'host' => 'foo.invalid-tld-zzz',
            'domain' => '',
            'tld' => '',
            'private' => false,
        ]);
    });
});
