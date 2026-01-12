<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Core\Result;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Support\Path;

final class Schema {
  /** @var array<string,Field> */
  private array $fields = [];

  /** @var array<string,Schema> path => subschema */
  private array $objectSchemas = [];

  /** @var array<string,Schema> wildcardPath => subschema */
  private array $eachObjectSchemas = [];

  public static function make(): self { return new self(); }

  public function field(string $path, Field $field): self {
    $this->fields[$path] = $field;
    return $this;
  }

  /** Convenience: apply a field to each element of an array at $arrayPath */
  public function each(string $arrayPath, Field $elementField): self {
    $arrayPath = trim($arrayPath);
    $path = rtrim($arrayPath, '.') . '.*';
    return $this->field($path, $elementField);
  }

  /**
   * Apply a child schema to an object at $path (e.g. "user").
   * The child schema uses relative paths (e.g. "email", "profile.name").
   */
  public function object(string $path, Schema $schema): self {
    $this->objectSchemas[$path] = $schema;
    return $this;
  }

  /**
   * Apply a child schema to each object in an array at $arrayPath (e.g. "items").
   * Equivalent to targeting "items.*" but at schema level.
   */
  public function eachObject(string $arrayPath, Schema $schema): self {
    $arrayPath = trim($arrayPath);
    $wildcardPath = rtrim($arrayPath, '.') . '.*';
    $this->eachObjectSchemas[$wildcardPath] = $schema;
    return $this;
  }

  public function process(array $input, Level $level): Result {
    $values = [];
    $errors = [];

    // 1) Field rules (including wildcards)
    foreach ($this->fields as $path => $field) {
      if (Path::hasWildcard($path)) {
        $matches = Path::expand($input, $path);

        foreach ($matches as $m) {
          [$value, $errs] = $field->process(
            $m['value'],
            $level,
            ['path' => $m['path'], 'input' => $input, 'level' => $level]
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
        ['path' => $path, 'input' => $input, 'level' => $level]
      );

      $values = Path::set($values, $path, $value);
      $errors = array_merge($errors, $errs);
    }

    // 2) Object schemas at fixed paths
    foreach ($this->objectSchemas as $path => $schema) {
      $raw = Path::get($input, $path);

      // If missing/null: let user enforce required via a field on same path if desired
      if ($raw === null) {
        continue;
      }

      if (!is_array($raw) || array_is_list($raw)) {
        // You can also enforce this via Type::object() field; this is a safe guardrail
        $errors[] = new Error($path, \RandomX98\InputGuard\Core\ErrorCode::OBJECT, null);
        continue;
      }

      $childResult = $schema->process($raw, $level);

      // put sanitized child object under the path
      $values = Path::set($values, $path, $childResult->values());

      // prefix errors
      foreach ($childResult->errors() as $e) {
        $prefixed = $this->prefixError($path, $e);
        $errors[] = $prefixed;
      }
    }

    // 3) Object schemas for each element (wildcard)
    foreach ($this->eachObjectSchemas as $wildPath => $schema) {
      $matches = Path::expand($input, $wildPath);

      foreach ($matches as $m) {
        $raw = $m['value'];
        $basePath = $m['path']; // concrete like items.0

        if ($raw === null) {
          continue;
        }

        if (!is_array($raw) || array_is_list($raw)) {
          $errors[] = new Error($basePath, \RandomX98\InputGuard\Core\ErrorCode::OBJECT, null);
          continue;
        }

        $childResult = $schema->process($raw, $level);

        $values = Path::set($values, $basePath, $childResult->values());

        foreach ($childResult->errors() as $e) {
          $errors[] = $this->prefixError($basePath, $e);
        }
      }
    }

    return new Result($values, $errors);
  }

  private function prefixError(string $prefix, Error $e): Error {
    $p = trim($prefix);
    $childPath = trim($e->path);

    $newPath = $childPath === '' ? $p : ($p === '' ? $childPath : $p . '.' . $childPath);

    return new Error($newPath, $e->code, $e->message, $e->meta);
  }
}