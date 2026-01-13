<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class TypeIntValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        if (!is_int($value)) {
            return [new Error($context['path'] ?? '', ErrorCode::INT, null, ['actual_type' => gettype($value)])];
        }
        return [];
    }
}