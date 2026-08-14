<?php

use function DomainValidity\Support\remove_comments;
use function DomainValidity\Support\remove_empty_lines;
use function DomainValidity\Support\validate_domain_root;

describe('remove_comments', function () {
    it('strips single-line comments', function () {
        expect(remove_comments("com // note\n// full line\nnet"))->toBe("com \n\nnet");
    });

    it('strips multi-line comments', function () {
        expect(remove_comments("a/* hidden */b"))->toBe('ab');
    });
});

describe('remove_empty_lines', function () {
    it('collapses blank lines', function () {
        expect(remove_empty_lines("com\n\n\nnet"))->toBe("com\nnet");
    });
});

describe('global BC shims', function () {
    it('still exposes the pre-3.1 global function names', function () {
        expect(\remove_comments('a // b'))->toBe('a ')
            ->and(\remove_empty_lines("a\n\nb"))->toBe("a\nb")
            ->and(\validate_domain_root('adro'))->toBeTrue();
    });
});

describe('validate_domain_root', function () {
    it('accepts alphanumeric labels with dots and dashes', function () {
        expect(validate_domain_root('my-site.sub'))->toBeTrue();
    });

    it('rejects an empty string', function () {
        expect(validate_domain_root(''))->toBeFalse();
    });

    it('rejects PCRE metacharacters and wildcards', function () {
        expect(validate_domain_root('*.adro'))->toBeFalse()
            ->and(validate_domain_root('a(b'))->toBeFalse();
    });
});
