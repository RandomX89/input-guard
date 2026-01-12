<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class MinValidator implements Validator {
    public function __construct(private readonly int $min) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_int($value)) return [];
        if ($value < $this->min) {
            return [new Error($context['path'] ?? '', ErrorCode::MIN, 'Value is too small', ['min' => $this->min])];
        }
        return [];
    }
}