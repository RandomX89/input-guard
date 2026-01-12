<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class NoControlCharsValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        // Control chars: ASCII 0-31 and 127 (DEL)
        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::NO_CONTROL_CHARS,
                'Control characters are not allowed'
            )];
        }

        return [];
    }
}
