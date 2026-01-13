<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MinLenValidator implements Validator {
    public function __construct(private int $min) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_string($value)) return [];
        if (mb_strlen($value) < $this->min) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MIN_LEN,
                null,
                ['min' => $this->min, 'actual' => mb_strlen($value)]
            )];
        }
        return [];
    }
}