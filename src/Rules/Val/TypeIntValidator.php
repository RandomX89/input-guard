<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class TypeIntValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        if (!is_int($value)) {
            return [new Error($context['path'] ?? '', ErrorCode::INT, 'Expected an integer')];
        }
        return [];
    }
}