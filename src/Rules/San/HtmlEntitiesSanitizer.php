<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class HtmlEntitiesSanitizer implements Sanitizer {
    public function __construct(
        private readonly int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        private readonly string $encoding = 'UTF-8'
    ) {}

    public function apply(mixed $value, array $context = []): mixed {
        if (!is_string($value)) return $value;

        return htmlentities($value, $this->flags, $this->encoding);
    }
}
