<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class RequiredValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        $present = $context['present'] ?? true;


        if (!$present) {
            return [new Error($context['path'] ?? '', ErrorCode::REQUIRED, null)];
        }

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