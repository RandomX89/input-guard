<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class TypeObjectValidator implements Validator {
  public function validate(mixed $value, array $context = []): array {
    if ($value === null) return [];

    if (!is_array($value)) {
      return [new Error($context['path'] ?? '', ErrorCode::OBJECT, null)];
    }

    // JSON object => associative array (not a list)
    if (array_is_list($value)) {
      return [new Error($context['path'] ?? '', ErrorCode::OBJECT, null)];
    }

    return [];
  }
}