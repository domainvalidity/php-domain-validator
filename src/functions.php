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
