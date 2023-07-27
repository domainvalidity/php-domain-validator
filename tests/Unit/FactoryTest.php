<?php

use DomainValidity\Factory;
use DomainValidity\Validator;


test('factory make', function () {
    $validator = Factory::make(
        getPublicSuffixListPath()
    );

    expect($validator)->toBeInstanceOf(Validator::class);
});
