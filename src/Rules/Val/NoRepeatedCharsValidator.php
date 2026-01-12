<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

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
