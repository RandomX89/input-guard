<?php
namespace InputGuard\Rules\San;

use InputGuard\Contract\Sanitizer;

final class NormalizeNfkcSanitizer implements Sanitizer {
    public function apply(mixed $value, array $context = []): mixed {
        if (!is_string($value)) return $value;

        if (!class_exists(\Normalizer::class)) {
            return $value;
        }

        $normalized = \Normalizer::normalize($value, \Normalizer::FORM_KC);
        if ($normalized === false) {
            return $value;
        }

        return $normalized;
    }
}
