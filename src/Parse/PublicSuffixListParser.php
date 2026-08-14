<?php

declare(strict_types=1);

namespace DomainValidity\Parse;

use function array_reverse;
use function explode;
use function preg_match;
use function preg_split;
use function str_contains;
use function str_starts_with;
use function substr;
use function trim;

class PublicSuffixListParser
{
    /**
     * Marks a node as the end of a rule. Contains a NUL byte so it can
     * never collide with a label from the list itself (rule lines
     * containing NUL bytes are discarded during parsing).
     */
    public const RULE_END = "\0end";

    /**
     * Marks a node as the end of an exception rule (a `!` rule).
     */
    public const RULE_EXCEPTION = "\0exception";

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
     * Extract the rule lines from a section: one rule per line, comments
     * (`//`), blank lines and anything after the first whitespace dropped,
     * per the Public Suffix List format specification.
     *
     * @return array<string>
     */
    protected static function extractRuleLines(string $pattern, string $publicSuffixList): array
    {
        $section = self::getSection($pattern, $publicSuffixList) ?? '';

        $rules = [];

        // Split on newlines explicitly: \R in byte mode also matches the
        // 0x85 (NEL) byte, which appears inside multi-byte UTF-8 labels
        foreach (preg_split('/\r\n|\r|\n/', $section) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '//') || str_contains($line, "\0")) {
                continue;
            }

            // The spec ignores everything after the first whitespace
            $rules[] = explode(' ', explode("\t", $line)[0])[0];
        }

        return $rules;
    }

    /**
     * Build a hierarchical nested map from rule lines.
     * Each rule is split by '.', reversed, and stored in nested arrays.
     * Complete rules are marked with RULE_END; `!` exception rules with
     * RULE_EXCEPTION.
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

            $isException = str_starts_with($line, '!');
            if ($isException) {
                $line = substr($line, 1);
                if ($line === '') {
                    continue;
                }
            }

            // Split rule by '.' and reverse (e.g., 'com.mx' -> ['mx', 'com'])
            $parts = array_reverse(explode('.', $line));

            // Navigate/create nested structure
            /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $current */
            $current = &$map;
            foreach ($parts as $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                /** @var array<string, true|array<string, true|array<string, true|array<string, true|array>>>> $current */
                $current = &$current[$part];
            }

            // Mark this as a complete rule
            $current[$isException ? self::RULE_EXCEPTION : self::RULE_END] = true;
        }

        return $map;
    }

    /**
     * @return array<'icann'|'private',
     *                array<string, true|array<string, true|array<string, true|array<string, true|array>>>>>
     */
    public static function parse(string $publicSuffixListString): array
    {
        return [
            'icann' => self::buildHierarchicalMap(
                self::extractRuleLines(self::ICANN_DELIMITER_PATTERN, $publicSuffixListString)
            ),
            'private' => self::buildHierarchicalMap(
                self::extractRuleLines(self::PRIVATE_DELIMITER_PATTERN, $publicSuffixListString)
            ),
        ];
    }
}
