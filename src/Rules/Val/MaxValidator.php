<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class MaxValidator implements Validator {
    public function __construct(private readonly int $max) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_int($value)) return [];
        if ($value > $this->max) {
            return [new Error($context['path'] ?? '', ErrorCode::MAX, 'Value is too large', ['max' => $this->max])];
        }
        return [];
    }
}