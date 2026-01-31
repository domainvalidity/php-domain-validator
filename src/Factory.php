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
        /** @var array<'icann'|'private'|string, array<string, true|array<string, true|array<string, true|array<string, true|array>>>>> $publicSuffixList */
        $publicSuffixList = PublicSuffixListParser::parse($dotDatContent);

        return new Validator($publicSuffixList);
    }
}
