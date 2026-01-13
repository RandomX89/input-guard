<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Detects suspicious input patterns that indicate bot/spam behavior:
 * - Excessive punctuation
 * - Too many numbers in text
 * - Suspicious character combinations
 */
final class NoSuspiciousPatternValidator implements Validator {
    public function __construct(
        private readonly float $maxPunctuationRatio = 0.3,
        private readonly float $maxDigitRatio = 0.4,
        private readonly int $minLengthToCheck = 10
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || mb_strlen($value) < $this->minLengthToCheck) {
            return [];
        }

        $len = mb_strlen($value);

        $punctuation = preg_match_all('/[!?.,;:@#$%^&*()_+=\[\]{}|\\\\<>\/~`"\'-]/u', $value);
        if ($punctuation / $len > $this->maxPunctuationRatio) {
            return [$this->error($context, 'excessive_punctuation')];
        }

        $digits = preg_match_all('/\d/', $value);
        if ($digits / $len > $this->maxDigitRatio) {
            return [$this->error($context, 'excessive_digits')];
        }

        if (preg_match('/(.)\1{5,}/u', $value)) {
            return [$this->error($context, 'repeated_sequence')];
        }

        if (preg_match('/[!?]{3,}/', $value)) {
            return [$this->error($context, 'excessive_exclamation')];
        }

        return [];
    }

    private function error(array $context, string $reason): Error {
        return new Error(
            $context['path'] ?? '',
            ErrorCode::SUSPICIOUS_PATTERN,
            null,
            ['reason' => $reason]
        );
    }
}
