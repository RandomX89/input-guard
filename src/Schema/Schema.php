<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Core\Result;
use RandomX98\InputGuard\Support\Path;

final class Schema {
  /** @var array<string,Field> */
  private array $fields = [];

  public static function make(): self { return new self(); }

  public function field(string $path, Field $field): self {
    $this->fields[$path] = $field;
    return $this;
  }

  /**
   * Convenience: apply a field to each element of an array at $arrayPath ("items" => "items.*")
   */
  public function each(string $arrayPath, Field $elementField): self {
    $arrayPath = trim($arrayPath);
    $path = rtrim($arrayPath, '.') . '.*';
    return $this->field($path, $elementField);
  }

  public function process(array $input, Level $level): Result {
    $values = [];
    $errors = [];

    foreach ($this->fields as $path => $field) {
      if (Path::hasWildcard($path)) {
        $matches = Path::expand($input, $path);

        foreach ($matches as $m) {
          [$value, $errs] = $field->process(
            $m['value'],
            $level,
            [
              'path' => $m['path'],   // concrete path like items.0.name
              'input' => $input,
              'level' => $level,
            ]
          );

          $values = Path::set($values, $m['path'], $value);
          $errors = array_merge($errors, $errs);
        }

        continue;
      }

      $raw = Path::get($input, $path);

      [$value, $errs] = $field->process(
        $raw,
        $level,
        [
          'path' => $path,
          'input' => $input,
          'level' => $level,
        ]
      );

      $values = Path::set($values, $path, $value);
      $errors = array_merge($errors, $errs);
    }

    return new Result($values, $errors);
  }
}