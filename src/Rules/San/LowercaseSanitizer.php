<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class LowercaseSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        return is_string($value) ? mb_strtolower($value) : $value;
    }
}