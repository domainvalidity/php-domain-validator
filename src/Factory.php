<?php

namespace DomainValidity;

use DomainValidity\Parse\PublicSuffixListParser;
use DomainValidity\Validator;
use InvalidArgumentException;

class Factory
{
    /**
     * @throws InvalidArgumentException
     */
    public static function make(string $dotDatContent): Validator
    {
        $publicSuffixList = PublicSuffixListParser::parse($dotDatContent);

        return new Validator($publicSuffixList);
    }
}
