<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class EmailValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null || $value === '') return [];
        if (!is_string($value)) return [new Error($context['path'] ?? '', ErrorCode::EMAIL, null, ['value' => $value])];

        $ok = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        if (!$ok) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::EMAIL,
                null,
                ['value' => $value]
            )];
        }
        return [];
    }
}