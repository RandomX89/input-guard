<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Requires a minimum number of words in the text
 */
final class MinWordCountValidator implements Validator {
    public function __construct(
        private readonly int $minWords
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $words = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);

        if ($count < $this->minWords) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MIN_WORDS,
                null,
                ['min' => $this->minWords, 'actual' => $count]
            )];
        }

        return [];
    }
}
