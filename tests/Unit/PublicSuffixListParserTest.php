<?php

use DomainValidity\Factory;
use DomainValidity\Parse\PublicSuffixListParser;

describe('PublicSuffixListParser structure', function () {
    it('returns empty maps for empty input', function () {
        expect(PublicSuffixListParser::parse(''))
            ->toBe(['icann' => [], 'private' => []]);
    });

    it('returns empty maps when section markers are missing', function () {
        expect(PublicSuffixListParser::parse("com\nnet\norg\n"))
            ->toBe(['icann' => [], 'private' => []]);
    });

    it('parses a minimal list into a hierarchical map', function () {
        $map = PublicSuffixListParser::parse(makeList("com\ncom.mx"));

        expect($map['icann']['com'][PublicSuffixListParser::RULE_END] ?? false)->toBeTrue()
            ->and($map['icann']['mx']['com'][PublicSuffixListParser::RULE_END] ?? false)->toBeTrue();
    });

    it('skips comment lines and blank lines inside sections', function () {
        $map = PublicSuffixListParser::parse(makeList("// a comment\n\ncom\n\n// another\nnet"));

        expect($map['icann'])->toHaveKeys(['com', 'net']);
    });

    it('ignores everything after the first whitespace on a rule line', function () {
        $map = PublicSuffixListParser::parse(makeList("com trailing junk"));

        expect($map['icann'])->toHaveKey('com');
    });

    it('stores exception rules with the exception marker', function () {
        $map = PublicSuffixListParser::parse(makeList("*.ck\n!www.ck"));

        expect($map['icann']['ck']['*'][PublicSuffixListParser::RULE_END] ?? false)->toBeTrue()
            ->and($map['icann']['ck']['www'][PublicSuffixListParser::RULE_EXCEPTION] ?? false)->toBeTrue();
    });

    it('does not split lines inside multi-byte UTF-8 labels', function () {
        // 酒 contains the 0x85 byte, which \R in byte mode treats as NEL
        $map = PublicSuffixListParser::parse(makeList("嘉里大酒店"));

        expect($map['icann'])->toHaveKey('嘉里大酒店');
    });
});

describe('PublicSuffixListParser hardening', function () {
    it('cannot be poisoned by a literal __end__ rule line', function () {
        $validator = Factory::make(makeList("__end__\ncom"));

        expect($validator->validate('example.com')->isValid())->toBeTrue()
            ->and($validator->validate('example.net')->isValid())->toBeFalse();
    });

    it('discards rule lines containing NUL bytes', function () {
        $map = PublicSuffixListParser::parse(makeList("com\n\0end"));

        expect($map['icann'])->toHaveKey('com')
            ->and($map['icann'])->not->toHaveKey("\0end");
    });
});
