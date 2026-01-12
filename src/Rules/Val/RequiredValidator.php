<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class RequiredValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null || (is_string($value) && $value === '')) {
            return [
                new Error(
                    path: $context['path'] ?? '',
                    code: ErrorCode::REQUIRED,
                    message: null,
                    meta: []
                )
            ];
        }
        return [];
    }
}