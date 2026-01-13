<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Blocks text with excessively repeated characters (e.g., "aaaaaaa", "!!!!!!")
 */
final class NoRepeatedCharsValidator implements Validator {
    public function __construct(
        private readonly int $maxRepeats = 4
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $pattern = '/(.)\1{' . $this->maxRepeats . ',}/u';

        if (preg_match($pattern, $value, $matches)) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::REPEATED_CHARS,
                null,
                ['char' => $matches[1], 'max' => $this->maxRepeats]
            )];
        }

        return [];
    }
}
