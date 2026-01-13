<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class TypeStringValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        if (!is_string($value)) {
            return [new Error($context['path'] ?? '', ErrorCode::STRING, null, ['actual_type' => gettype($value)])];
        }
        return [];
    }
}