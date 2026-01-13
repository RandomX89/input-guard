<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Limits maximum number of words in the text
 */
final class MaxWordCountValidator implements Validator {
    public function __construct(
        private readonly int $maxWords
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $words = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);

        if ($count > $this->maxWords) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MAX_WORDS,
                null,
                ['max' => $this->maxWords, 'actual' => $count]
            )];
        }

        return [];
    }
}
