<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class NoShellCharsValidator implements Validator {
    private const DANGEROUS_CHARS = [
        ';',
        '|',
        '&',
        '`',
        '$',
        '(',
        ')',
        '{',
        '}',
        '[',
        ']',
        '<',
        '>',
        '!',
        "\n",
        "\r",
    ];

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        foreach (self::DANGEROUS_CHARS as $char) {
            if (str_contains($value, $char)) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::NO_SHELL_CHARS,
                    null,
                    ['detected' => $char]
                )];
            }
        }

        return [];
    }
}
