<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class ToIntSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        if ($value === null) return null;

        if (is_int($value)) return $value;

        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return null;
            if (!preg_match('/^-?\d+$/', $v)) return $value; // leave as-is for validator to flag
            return (int)$v;
        }

        return $value;
    }
}