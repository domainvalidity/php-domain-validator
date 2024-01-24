<?php

declare(strict_types=1);

function remove_comments(string $text): ?string
{
    // Remove single-line comments
    $text = preg_replace('/\/\/.*$/m', '', $text);

    if ($text !== null) {
        // Remove multi-line comments
        $text = preg_replace('/\/\*(.*?)\*\//s', '', $text);
    }


    return $text;
}

function remove_empty_lines(string $text): ?string
{
    // Remove empty lines
    $text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);

    return $text;
}

function validate_domain_root(string $root): bool
{
    // Check if the string only contains alphanumeric values, dots or dashes
    return preg_match('/^[a-zA-Z0-9.-]+$/', $root) === 1;
}
