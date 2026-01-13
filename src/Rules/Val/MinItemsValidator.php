<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MinItemsValidator implements Validator {
  public function __construct(private int $min) {}

  public function validate(mixed $value, array $context = []): array {
    if ($value === null) return [];
    if (!is_array($value)) return []; // lasciamo a TypeArrayValidator il compito del type error

    $count = count($value);
    if ($count < $this->min) {
      return [new Error(
        $context['path'] ?? '',
        ErrorCode::MIN_ITEMS,
        null,
        ['min' => $this->min, 'count' => $count]
      )];
    }
    return [];
  }
}