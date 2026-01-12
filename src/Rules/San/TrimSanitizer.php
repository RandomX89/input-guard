<?php
namespace RandomX98\InputGuard\Rules\San;

use RandomX98\InputGuard\Contract\Sanitizer;

final class TrimSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        return is_string($value) ? trim($value) : $value;
    }
}