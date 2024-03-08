<?php

use DomainValidity\Host\Host;

describe('Valid adro.com.mx', function () {
    $validator = getInstance();
    $url = 'https://www.adro.com.mx/blog?author=adro&tag=php&tag=laravel';
    $host = $validator->validate($url);

    it('is instance of host', fn () =>  expect($host)->toBeInstanceOf(Host::class));
    it('is a valid domain', fn () =>  expect($host->isValid())->toBeTrue());
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
    it('com.mx is the TLD', fn () =>  expect($host->tld())->toBe('com.mx'));
    it('adro.com.mx is the domain', fn () =>  expect($host->domain())->toBe('adro.com.mx'));
    it('www.adro.com.mx is the host', fn () =>  expect($host->toString())->toBe('www.adro.com.mx'));
    it('is not private', fn () =>  expect($host->isPrivate())->toBe(false));
});

describe('Valid adro.嘉里大酒店', function () {
    $validator = getInstance();
    $url = 'www.adro.嘉里大酒店';
    $host = $validator->validate($url);

    it('is a valid domain', fn () =>  expect($host->isValid())->toBeTrue());
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
    it('嘉里大酒店 is the TLD', fn () =>  expect($host->tld())->toBe('嘉里大酒店'));
    it('adro.嘉里大酒店 is the domain', fn () =>  expect($host->domain())->toBe('adro.嘉里大酒店'));
    it('www.adro.嘉里大酒店 is the host', fn () =>  expect($host->toString())->toBe('www.adro.嘉里大酒店'));
    it('is not private', fn () =>  expect($host->isPrivate())->toBe(false));
});

describe('Valid and private d-sd8krc91u2.execute-api.us-west-1.amazonaws.com', function () {
    $validator = getInstance();
    $url = 'd-sd8krc91u2.execute-api.us-west-1.amazonaws.com';
    $host = $validator->validate($url);

    it('is a valid domain', fn () =>  expect($host->isValid())->toBeTrue());
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
    it('com is the TLD', fn () =>  expect($host->tld())->toBe('com'));
    it('amazonaws.com is the domain', fn () =>  expect($host->domain())->toBe('amazonaws.com'));
    it('d-sd8krc91u2.execute-api.us-west-1.amazonaws.com is the host', fn () =>  expect($host->toString())
        ->toBe('d-sd8krc91u2.execute-api.us-west-1.amazonaws.com'));
    it('is private', fn () =>  expect($host->isPrivate())->toBeTrue());
});

describe('Invalid adro.is.a.rocker.and', function () {
    $validator = getInstance();
    $url = 'https://adro.is.a.rocker.and/he-rocks?or=not';
    $host = $validator->validate($url);

    it('is not a valid domain', fn () =>  expect($host->isValid())->toBeFalse());
    it('empty domain', fn () =>  expect($host->domain())->toBe(''));
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
});

describe('Valid claudiatreagus.co.za', function () {
    $validator = getInstance();
    $url = 'https://claudiatreagus.co.za/';
    $host = $validator->validate($url);

    it('is a valid domain', fn () =>  expect($host->isValid())->toBeTrue());
    it('is same domain', fn () =>  expect($host->domain())->toBe('claudiatreagus.co.za'));
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
});

describe('Invalid *.adro.com', function () {
    $validator = getInstance();
    $url = 'https://*.adro.com';
    $host = $validator->validate($url);

    it('is not a valid domain', fn () =>  expect($host->isValid())->toBeFalse());
    it('is not empty domain', fn () =>  expect($host->domain())->toBe('adro.com'));
    it("is same as $url", fn () =>  expect($host->original())->toBe($url));
});

describe('Root domain is correctly obtained when the tld substring is contained in it', function () {
    $validator = getInstance();
    $url = 'a.b.c.compass.com';
    $host = $validator->validate($url);

    it("$url is valid domain", fn () => expect($host->isValid())->toBeTrue());
    it("from $url - compass.com is the domain", fn () => expect($host->domain())->toBe('compass.com'));

    $validator = getInstance();
    $url = 'compass.com';
    $host = $validator->validate($url);

    it("$url is valid domain", fn () => expect($host->isValid())->toBeTrue());
    it("from $url - compass.com is the domain", fn () => expect($host->domain())->toBe('compass.com'));
});
