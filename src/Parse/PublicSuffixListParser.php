<?php

declare(strict_types=1);

namespace DomainValidity\Parse;

use function explode;
use function preg_match;
use function remove_comments;
use function remove_empty_lines;
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
     * Build a hierarchical nested map from domain lines.
     * Each domain is split by '.', reversed, and stored in nested arrays.
     * Uses '__end__' marker to indicate complete domain entries.
     *
     * @param array<string> $lines
     * @return array<string, true|array<string, true|array<string, true|array<string, true|array>>>>
     */
    private static function buildHierarchicalMap(array $lines): array
    {
        $map = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Split domain by '.' and reverse (e.g., 'com.mx' -> ['mx', 'com'])
            $parts = array_reverse(explode('.', $line));

            // Navigate/create nested structure
            /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $current */
            $current = &$map;
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $current */
                $current = &$current[$part];
            }

            // Mark this as a complete domain entry
            $current['__end__'] = true;
        }

        return $map;
    }

    /**
     * @return array<'icann'|'private',
     *                array<string, true|array<string, true|array<string, true|array<string, true|array>>>>>
     */
    public static function parse(string $publicSuffixListString): array
    {
        $icannSection = self::getSection(self::ICANN_DELIMITER_PATTERN, $publicSuffixListString);

        $privateSection = self::getSection(self::PRIVATE_DELIMITER_PATTERN, $publicSuffixListString);

         $icann = remove_empty_lines(
             (string) remove_comments($icannSection ?? '')
         );

         $private = remove_empty_lines(
             (string) remove_comments($privateSection ?? '')
         );

        $icannLines = explode("\n", $icann ?? '');
        $privateLines = explode("\n", $private ?? '');

        unset($icannLines[0]);
        unset($privateLines[0]);

        $icannLines = array_values($icannLines);
        $privateLines = array_values($privateLines);

        return [
            'icann' => self::buildHierarchicalMap($icannLines),
            'private' => self::buildHierarchicalMap($privateLines),
        ];
    }
}
