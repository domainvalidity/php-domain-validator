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

        $list = array_values($list);

        $fn = function (string $tld) {
            return boolval(
                preg_match(
                    "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i",
                    $tld
                )
            );
        };

        foreach ($list as $key => $tld) {
            if ($fn($tld) === false) {
                unset($list[$key]);
            }
        }

        return $list;
    }
}
