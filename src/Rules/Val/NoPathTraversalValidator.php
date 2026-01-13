<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class NoPathTraversalValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $dangerous = [
            '../',
            '..\\',
            '%2e%2e%2f',
            '%2e%2e/',
            '..%2f',
            '%2e%2e%5c',
            '%00',
            '\0',
        ];

        $lower = strtolower($value);
        foreach ($dangerous as $pattern) {
            if (str_contains($lower, strtolower($pattern))) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::NO_PATH_TRAVERSAL,
                    null,
                    ['detected' => $pattern]
                )];
            }
        }

        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::NO_PATH_TRAVERSAL,
                null,
                ['detected' => 'windows_drive']
            )];
        }

        if (preg_match('#^[/\\\\]#', $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::NO_PATH_TRAVERSAL,
                null,
                ['detected' => 'absolute_path']
            )];
        }

        return [];
    }
}
