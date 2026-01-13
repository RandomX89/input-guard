<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class SafeUrlValidator implements Validator {
    /** @param string[] $allowedSchemes */
    public function __construct(
        private readonly array $allowedSchemes = ['http', 'https']
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $dangerous = [
            'javascript:',
            'vbscript:',
            'data:',
            'file:',
        ];

        $lower = strtolower(trim($value));
        foreach ($dangerous as $scheme) {
            if (str_starts_with($lower, $scheme)) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::SAFE_URL,
                    null,
                    ['detected' => $scheme]
                )];
            }
        }

        $parsed = parse_url($value);
        if ($parsed === false) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::SAFE_URL,
                null,
                ['reason' => 'invalid_url']
            )];
        }

        if (isset($parsed['scheme'])) {
            $scheme = strtolower($parsed['scheme']);
            if (!in_array($scheme, $this->allowedSchemes, true)) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::SAFE_URL,
                    null,
                    ['detected' => $scheme, 'allowed' => $this->allowedSchemes]
                )];
            }
        }

        return [];
    }
}
