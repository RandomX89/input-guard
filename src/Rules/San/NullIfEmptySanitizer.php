<?php
namespace RandomX98\InputGuard\Rules\San;

use RandomX98\InputGuard\Contract\Sanitizer;

final class NullIfEmptySanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        if ($value === null) return null;
        if (is_string($value) && $value === '') return null;
        return $value;
    }
}