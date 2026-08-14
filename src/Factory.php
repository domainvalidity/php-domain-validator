<?php

namespace DomainValidity;

use DomainValidity\Parse\PublicSuffixListParser;

final class Factory
{
    public static function make(string $dotDatContent): Validator
    {
        /** @var array<'icann'|'private'|string, array<string, true|array<string, true|array<string, true|array<string, true|array>>>>> $publicSuffixList */
        $publicSuffixList = PublicSuffixListParser::parse($dotDatContent);

        return new Validator($publicSuffixList);
    }
}
