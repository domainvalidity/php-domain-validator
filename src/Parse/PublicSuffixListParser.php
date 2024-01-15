<?php

declare(strict_types=1);

namespace DomainValidity\Parse;

use function explode;
use function preg_match;
use function remove_comments;
use function remove_empty_lines;
use function strval;
use function trim;

class PublicSuffixListParser
{
    private const ICANN_DELIMITER_PATTERN = '/\/\/ ===BEGIN ICANN DOMAINS===(.*?)\/\/ ===END ICANN DOMAINS===/s';

    private const PRIVATE_DELIMITER_PATTERN = '/\/\/ ===BEGIN PRIVATE DOMAINS===(.*?)\/\/ ===END PRIVATE DOMAINS===/s';

    protected static function getSection(string $pattern, string $publicSuffixList): ?string
    {
        preg_match($pattern, $publicSuffixList, $matches);

        $section = null;

        // $matches[1] contains the content between the markers
        if (isset($matches[1])) {
            $section = trim($matches[1]);
        }

        return $section;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function parse(string $publicSuffixListString): array
    {
        $icannSection = strval(self::getSection(self::ICANN_DELIMITER_PATTERN, $publicSuffixListString));

        $privateSection = strval(self::getSection(self::PRIVATE_DELIMITER_PATTERN, $publicSuffixListString));

        $icann = remove_empty_lines(
            strval(remove_comments($icannSection))
        );

        $private = remove_empty_lines(
            strval(remove_comments($privateSection))
        );

        $icannLines = explode("\n", strval($icann));
        $privateLines = explode("\n", strval($private));

        unset($icannLines[0]);
        unset($privateLines[0]);

        return ['icann' => array_values($icannLines), 'private' => array_values($privateLines)];
    }
}
