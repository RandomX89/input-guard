<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class MaxBytesValidator implements Validator {
    public function __construct(
        private readonly int $maxBytes
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value)) {
            return [];
        }

        $bytes = strlen($value);
        if ($bytes > $this->maxBytes) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::MAX_BYTES,
                null,
                ['max' => $this->maxBytes, 'actual' => $bytes]
            )];
        }

        return [];
    }
}
