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
    public static function make(string $datFile): Validator
    {
        $content = file_get_contents($datFile);

        if ($content === false) {
            throw new InvalidArgumentException();
        }

        $publicSuffixList = PublicSuffixListParser::parse($content);

        return new Validator($publicSuffixList);
    }
}
