<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class InSetValidator implements Validator {
    /** @param array<int,string|int> $allowed */
    public function __construct(
        private readonly array $allowed,
        private readonly bool $strict = true
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        $ok = in_array($value, $this->allowed, $this->strict);

        if (!$ok) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::IN_SET,
                null,
                ['allowed' => $this->allowed, 'value' => $value]
            )];
        }

        return [];
    }
}