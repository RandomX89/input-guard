<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Blocks common spam keywords and patterns
 */
final class NoSpamKeywordsValidator implements Validator {
    private const DEFAULT_PATTERNS = [
        '/\bcasino\b/i',
        '/\bpoker\b/i',
        '/\bviagra\b/i',
        '/\bcialis\b/i',
        '/\bcrypto\s*currency/i',
        '/\bbitcoin\s*investment/i',
        '/\bmake\s*money\s*fast/i',
        '/\bget\s*rich\s*quick/i',
        '/\bfree\s*money/i',
        '/\bclick\s*here\s*now/i',
        '/\blimited\s*time\s*offer/i',
        '/\bact\s*now/i',
        '/\b100%\s*free\b/i',
        '/\bno\s*obligation/i',
        '/\bcongratulations.*won/i',
        '/\bwinner.*selected/i',
        '/\bunsubscribe/i',
        '/\bSEO\s*services/i',
        '/\bbacklinks/i',
        '/\bwebsite\s*traffic/i',
    ];

    /** @param string[] $additionalPatterns */
    public function __construct(
        private readonly array $additionalPatterns = [],
        private readonly bool $useDefaults = true
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $patterns = $this->useDefaults
            ? array_merge(self::DEFAULT_PATTERNS, $this->additionalPatterns)
            : $this->additionalPatterns;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::SPAM_KEYWORDS,
                    null,
                    ['pattern' => $pattern]
                )];
            }
        }

        return [];
    }
}
