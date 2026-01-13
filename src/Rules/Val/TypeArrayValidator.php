<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class TypeArrayValidator implements Validator {
  public function validate(mixed $value, array $context = []): array {
    if ($value === null) return [];
    if (!is_array($value)) {
      return [new Error($context['path'] ?? '', ErrorCode::ARRAY, null)];
    }
    return [];
  }
}