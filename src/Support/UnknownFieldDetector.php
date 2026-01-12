<?php
namespace RandomX98\InputGuard\Support;

use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class UnknownFieldDetector {
  /** @return Error[] */
  public static function detect(array $input, SchemaSpecNode $spec): array {
    $errors = [];
    self::walk($input, $spec, '', $errors);
    return $errors;
  }

  /** @param Error[] $errors */
  private static function walk(mixed $value, SchemaSpecNode $spec, string $prefix, array &$errors): void {
    if (!is_array($value)) {
      return;
    }

    foreach ($value as $k => $v) {
      $key = (string)$k;
      $path = $prefix === '' ? $key : ($prefix . '.' . $key);

      $child = $spec->children[$key] ?? $spec->wildcard;
      if (!$child) {
        $errors[] = new Error($path, ErrorCode::UNKNOWN_FIELD, null, []);
        continue;
      }

      if ($child->allowsNested()) {
        self::walk($v, $child, $path, $errors);
      }
    }
  }
}
