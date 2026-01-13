<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

/**
 * Blocks text that is mostly or entirely UPPERCASE (shouting)
 */
final class NoAllCapsValidator implements Validator {
    public function __construct(
        private readonly float $maxUppercaseRatio = 0.7,
        private readonly int $minLengthToCheck = 10
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $letters = preg_replace('/[^a-zA-ZàèéìòùáéíóúÀÈÉÌÒÙ]/u', '', $value);
        if (mb_strlen($letters) < $this->minLengthToCheck) {
            return [];
        }

        $upper = preg_replace('/[^A-ZÀÈÉÌÒÙ]/u', '', $value);
        $ratio = mb_strlen($upper) / mb_strlen($letters);

        if ($ratio > $this->maxUppercaseRatio) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::ALL_CAPS,
                null,
                ['ratio' => round($ratio, 2)]
            )];
        }

        return [];
    }
}
