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

// public function testValidate(): void
// {
//     $mock = new MockHandler ([
//         new Response(
//             status: 200,
//             headers: ['content-type' => 'application/json'],
//             body: '{"valid":true,"original":"https:\/\/api.domainvalidity.dev","host":"api.domainvalidity.dev","domain":"domainvalidity.dev","tld":"dev"}'
//         ),
//     ]);
//     $handlerStack = HandlerStack::create($mock);
//     $client = new Client([
//         'handler' => $handlerStack,
//         'base_uri' => Factory::PRODUCTION_HOST,
//     ]);
//     $validator = new Validator($client);
//     $host = $validator->validate(Factory::PRODUCTION_HOST);
//     $this->assertInstanceOf(Host::class, $host);
//     $this->assertSame(Factory::PRODUCTION_HOST, $host->original());
//     $this->assertSame('dev', $host->tld());
//     $this->assertSame('domainvalidity.dev', $host->domain());
// }