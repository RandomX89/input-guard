<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MaxLenValidator implements Validator {
    public function __construct(private int $max) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null || !is_string($value)) return [];
        if (mb_strlen($value) > $this->max) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MAX_LEN,
                null,
                ['max' => $this->max, 'actual' => mb_strlen($value)]
            )];
        }
        return [];
    }
}