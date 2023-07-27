<?php

use DomainValidity\Factory;
use DomainValidity\Validator;

test('instance', function () {
    $validator = Factory::make(
        getPublicSuffixListPath()
    );

    expect($validator)->toBeInstanceOf(Validator::class);
});

/** @var \PHPUnit\Framework\TestCase $this */
test('validate domain', function () {
    $validator = Factory::make(
        getPublicSuffixListPath()
    );

    $domainName = 'https://www.adro.com.mx/';

    $array = $validator->validate($domainName);

    expect($array)->toBeArray();


    $this->assertSame($domainName, $array['original']);
    $this->assertSame('com.mx', $array['tld']);
    $this->assertSame('www.adro.com.mx', $array['host']);
});
