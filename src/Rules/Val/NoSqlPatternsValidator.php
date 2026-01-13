<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class NoSqlPatternsValidator implements Validator {
    private const PATTERNS = [
        '/\bUNION\s+(ALL\s+)?SELECT\b/i',
        '/\bSELECT\s+.*\s+FROM\b/i',
        '/\bINSERT\s+INTO\b/i',
        '/\bUPDATE\s+.*\s+SET\b/i',
        '/\bDELETE\s+FROM\b/i',
        '/\bDROP\s+(TABLE|DATABASE|INDEX)\b/i',
        '/\bTRUNCATE\s+TABLE\b/i',
        '/\bALTER\s+TABLE\b/i',
        '/\bEXEC(UTE)?\s*\(/i',
        '/\bxp_\w+/i',
        '/;\s*--/',
        '/\bOR\s+1\s*=\s*1\b/i',
        '/\bOR\s+[\'"].*[\'"]\s*=\s*[\'"].*[\'"]/i',
        '/\'\s*OR\s+\'/i',
    ];

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::NO_SQL_PATTERNS,
                    null,
                    ['pattern' => $pattern]
                )];
            }
        }

        return [];
    }
}
