<?php
namespace InputGuard\Rules\Val;

use InputGuard\Contract\Validator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

final class MaxItemsValidator implements Validator {
  public function __construct(private int $max) {}

  public function validate(mixed $value, array $context = []): array {
    if ($value === null) return [];
    if (!is_array($value)) return [];

    $count = count($value);
    if ($count > $this->max) {
      return [new Error(
        $context['path'] ?? '',
        ErrorCode::MAX_ITEMS,
        null,
        ['max' => $this->max, 'count' => $count]
      )];
    }
    return [];
  }
}