<?php

use DomainValidity\Factory;
use DomainValidity\Validator;


test('factory make', function () {
    $validator = getInstance();

    expect($validator)->toBeInstanceOf(Validator::class);
});
