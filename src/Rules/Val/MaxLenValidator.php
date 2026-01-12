<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

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