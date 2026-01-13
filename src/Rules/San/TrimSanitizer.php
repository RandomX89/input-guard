<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class TrimSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        return is_string($value) ? trim($value) : $value;
    }
}