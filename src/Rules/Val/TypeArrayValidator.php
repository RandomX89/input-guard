<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class TypeArrayValidator implements Validator {
  public function validate(mixed $value, array $context = []): array {
    if ($value === null) return [];
    if (!is_array($value)) {
      return [new Error($context['path'] ?? '', ErrorCode::ARRAY, null)];
    }
    return [];
  }
}