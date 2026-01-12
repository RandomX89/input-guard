<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class EmailValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null || $value === '') return [];
        if (!is_string($value)) return [new Error($context['path'] ?? '', ErrorCode::EMAIL, 'Invalid email')];

        $ok = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        if (!$ok) {
            return [new Error($context['path'] ?? '', ErrorCode::EMAIL, 'Invalid email')];
        }
        return [];
    }
}