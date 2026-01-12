<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

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
