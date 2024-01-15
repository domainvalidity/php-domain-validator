<?php

use DomainValidity\Factory;
use DomainValidity\Validator;


test(Factory::class . '::make produce instance of ' . Validator::class, function () {
    $validator = getInstance();

    expect($validator)->toBeInstanceOf(Validator::class);
});
