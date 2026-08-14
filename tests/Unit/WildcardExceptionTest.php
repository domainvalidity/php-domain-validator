<?php

describe('ICANN wildcard rules (*.ck)', function () {
    $validator = getInstance();
    $host = $validator->validate('foo.bar.ck');

    it('is a valid domain', fn () => expect($host->isValid())->toBeTrue());
    it('bar.ck is the TLD (wildcard match)', fn () => expect($host->tld())->toBe('bar.ck'));
    it('foo.bar.ck is the domain', fn () => expect($host->domain())->toBe('foo.bar.ck'));
});

describe('ICANN exception rules (!www.ck)', function () {
    $validator = getInstance();
    $host = $validator->validate('www.ck');

    it('is a valid domain', fn () => expect($host->isValid())->toBeTrue());
    it('ck is the TLD (exception rule prevails over *.ck)', fn () => expect($host->tld())->toBe('ck'));
    it('www.ck is the domain', fn () => expect($host->domain())->toBe('www.ck'));
});

describe('ICANN exception rules (!city.kawasaki.jp)', function () {
    $validator = getInstance();
    $host = $validator->validate('foo.city.kawasaki.jp');

    it('is a valid domain', fn () => expect($host->isValid())->toBeTrue());
    it('kawasaki.jp is the TLD', fn () => expect($host->tld())->toBe('kawasaki.jp'));
    it('city.kawasaki.jp is the domain', fn () => expect($host->domain())->toBe('city.kawasaki.jp'));
});

describe('Wildcard sibling under kawasaki.jp still applies', function () {
    $validator = getInstance();
    $host = $validator->validate('foo.other.kawasaki.jp');

    it('is a valid domain', fn () => expect($host->isValid())->toBeTrue());
    it('other.kawasaki.jp is the TLD (*.kawasaki.jp)', fn () => expect($host->tld())->toBe('other.kawasaki.jp'));
    it('foo.other.kawasaki.jp is the domain', fn () => expect($host->domain())->toBe('foo.other.kawasaki.jp'));
});

describe('Private wildcard rules (*.compute.amazonaws.com)', function () {
    $validator = getInstance();
    $host = $validator->validate('x.eu-west-1.compute.amazonaws.com');

    it('is a valid domain', fn () => expect($host->isValid())->toBeTrue());
    it('is private', fn () => expect($host->isPrivate())->toBeTrue());
    it('com is the TLD', fn () => expect($host->tld())->toBe('com'));
});

describe('Bare TLD is not a registrable domain', function () {
    $validator = getInstance();
    $host = $validator->validate('com');

    it('is not a valid domain', fn () => expect($host->isValid())->toBeFalse());
});
