<?php
namespace RandomX98\InputGuard\Rules\San;

use RandomX98\InputGuard\Contract\Sanitizer;

final class LowercaseSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        return is_string($value) ? mb_strtolower($value) : $value;
    }
}