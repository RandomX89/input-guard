<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Blocks text with too many URLs (common spam pattern)
 */
final class NoExcessiveUrlsValidator implements Validator {
    private const URL_PATTERN = '/(https?:\/\/|www\.)[^\s]+/i';

    public function __construct(
        private readonly int $maxUrls = 2
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        preg_match_all(self::URL_PATTERN, $value, $matches);
        $urlCount = count($matches[0]);

        if ($urlCount > $this->maxUrls) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::EXCESSIVE_URLS,
                null,
                ['max' => $this->maxUrls, 'actual' => $urlCount]
            )];
        }

        return [];
    }
}
