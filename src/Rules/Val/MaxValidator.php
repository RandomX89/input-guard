<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MaxValidator implements Validator {
    public function __construct(private readonly int $max) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_int($value)) return [];
        if ($value > $this->max) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MAX,
                null,
                ['max' => $this->max, 'actual' => $value]
            )];
        }
        return [];
    }
}