<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;

final class RequiredValidator implements Validator {
    public function validate(mixed $value, array $context = []): array {
        if ($value === null) {
            return [new Error($context['path'] ?? '', 'required', 'Value is required')];
        }
        if (is_string($value) && $value === '') {
            return [new Error($context['path'] ?? '', 'required', 'Value is required')];
        }
        return [];
    }
}