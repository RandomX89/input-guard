<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

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
