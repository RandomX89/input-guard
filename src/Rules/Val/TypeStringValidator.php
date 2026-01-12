<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class TypeStringValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        if (!is_string($value)) {
            return [new Error($context['path'] ?? '', ErrorCode::STRING, 'Expected a string')];
        }
        return [];
    }
}