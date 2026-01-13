<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MinValidator implements Validator {
    public function __construct(private readonly int $min) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_int($value)) return [];
        if ($value < $this->min) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MIN,
                null,
                ['min' => $this->min, 'actual' => $value]
            )];
        }
        return [];
    }
}