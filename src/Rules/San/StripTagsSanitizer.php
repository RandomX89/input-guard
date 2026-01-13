<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class StripTagsSanitizer implements Sanitizer {
    /** @param string[] $allowedTags */
    public function __construct(
        private readonly array $allowedTags = []
    ) {}

    public function apply(mixed $value, array $context = []): mixed {
        if (!is_string($value)) return $value;

        if ($this->allowedTags === []) {
            return strip_tags($value);
        }

        $allowed = implode('', array_map(fn($t) => "<$t>", $this->allowedTags));
        return strip_tags($value, $allowed);
    }
}
