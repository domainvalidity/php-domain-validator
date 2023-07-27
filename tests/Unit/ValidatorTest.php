<?php

use DomainValidity\Host\Host;

describe('Validator', function () {
    $validator = getInstance();
    $url = 'https://www.adro.com.mx/';
    $host = $validator->validate($url);

    it('is instance of host', fn () =>  expect($host)->toBeInstanceOf(Host::class));
    it('is a valid domain', fn () =>  expect($host->isValid())->toBeTrue());
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
    it('com.mx is the TLD', fn () =>  expect($host->tld())->toBe('com.mx'));
    it('adro.com.mx is the domain', fn () =>  expect($host->domain())->toBe('adro.com.mx'));
    it('www.adro.com.mx is the host', fn () =>  expect($host->toString())->toBe('www.adro.com.mx'));
});

describe('Validator fail', function () {
    $validator = getInstance();
    $url = 'https://adro.is.a.rocker.and/he-rocks?or=not';
    $host = $validator->validate($url);

    it('is not a valid domain', fn () =>  expect($host->isValid())->toBeFalse());
});
