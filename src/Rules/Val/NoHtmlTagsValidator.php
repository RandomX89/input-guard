<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class NoHtmlTagsValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        if (preg_match('/<[^>]*>/', $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::NO_HTML_TAGS,
                null,
                []
            )];
        }

        return [];
    }
}
