<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class PrintableOnlyValidator implements Validator {
    public function __construct(
        private readonly bool $allowNewlines = true,
        private readonly bool $allowTabs = true
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $allowed = ' -~';
        if ($this->allowNewlines) {
            $allowed .= '\n\r';
        }
        if ($this->allowTabs) {
            $allowed .= '\t';
        }

        $pattern = '/[^' . $allowed . ']/';

        if (preg_match($pattern, $value) === 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::PRINTABLE_ONLY,
                null,
                []
            )];
        }

        return [];
    }
}
