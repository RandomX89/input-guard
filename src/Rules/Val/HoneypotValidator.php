<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

/**
 * Honeypot field validator - the field MUST be empty.
 * Bots often fill all fields, so a hidden field that should be empty
 * catches automated submissions.
 */
final class HoneypotValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value !== null && $value !== '') {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::HONEYPOT,
                null,
                []
            )];
        }

        return [];
    }
}
