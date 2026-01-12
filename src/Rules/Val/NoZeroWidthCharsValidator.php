<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class NoZeroWidthCharsValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        if (preg_match('/[\x{200B}-\x{200D}\x{FEFF}]/u', $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::NO_ZERO_WIDTH_CHARS,
                null,
                []
            )];
        }

        return [];
    }
}
