<?php

namespace DomainValidity\Parse;

class PublicSuffixListParser
{
    /**
     * @return array<string>
     */
    public static function parse(string $publicSuffixListPath): array
    {
        $publicSuffixListPath = preg_replace('/\/\/\s(.*)/', '', $publicSuffixListPath);
        $publicSuffixListPath = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", strval($publicSuffixListPath));
        $list = explode("\n", strval($publicSuffixListPath));
        unset($list[0]);
        unset($list[count($list)]);

        return array_values($list);
    }
}
