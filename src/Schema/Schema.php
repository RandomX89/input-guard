<?php
namespace InputGuard\Schema;

use InputGuard\Core\Level;
use InputGuard\Core\Result;
use InputGuard\Core\Error;
use InputGuard\Support\Path;
use InputGuard\Support\SchemaSpecNode;
use InputGuard\Support\UnknownFieldDetector;

final class Schema {

  private string $policyVersion = '0.0.0';

  private bool $disallowOverlaps = false;

  private bool $rejectUnknown = false;

  /** @var array<string,Field> */
  private array $fields = [];

  /** @var array<string,Schema> path => subschema */
  private array $objectSchemas = [];

  /** @var array<string,Schema> wildcardPath => subschema */
  private array $eachObjectSchemas = [];

  /** @var \InputGuard\Contract\SchemaValidator[] */
  private array $schemaValidators = [];

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

    if ($this->disallowOverlaps && $this->hasOverlap()) {
      throw new \LogicException('Schema contains overlapping definitions between fields and nested object schemas.');
    }

    $values = [];
    $errors = [];

    // 1) Object schemas at fixed paths
    foreach ($this->objectSchemas as $path => $schema) {
      $raw = Path::get($input, $path);

      // If missing/null: let user enforce required via a field on same path if desired
      if ($raw === null) {
        continue;
      }

      if (!is_array($raw) || array_is_list($raw)) {
        // You can also enforce this via Type::object() field; this is a safe guardrail
        $errors[] = new Error($path, \InputGuard\Core\ErrorCode::OBJECT, null);
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

    // 2) Object schemas for each element (wildcard)
    foreach ($this->eachObjectSchemas as $wildPath => $schema) {
      $matches = Path::expand($input, $wildPath);

      foreach ($matches as $m) {
        $raw = $m['value'];
        $basePath = $m['path']; // concrete like items.0

        if ($raw === null) {
          continue;
        }

        if (!is_array($raw) || array_is_list($raw)) {
          $errors[] = new Error($basePath, \InputGuard\Core\ErrorCode::OBJECT, null);
          continue;
        }

        $childResult = $schema->process($raw, $level);

        $values = Path::set($values, $basePath, $childResult->values());

        foreach ($childResult->errors() as $e) {
          $errors[] = $this->prefixError($basePath, $e);
        }
      }
    }

    // 3) Field rules (including wildcards)
    // Fields run last so they can refine/override what object/eachObject schemas produced.
    $source = array_replace_recursive($input, $values);

    foreach ($this->fields as $path => $field) {
      if (Path::hasWildcard($path)) {
        $matches = Path::expand($input, $path);

        foreach ($matches as $m) {
          $raw = $m['present'] ? Path::get($source, $m['path']) : null;

          [$value, $errs] = $field->process(
            $raw,
            $level,
            ['path' => $m['path'], 'input' => $input, 'level' => $level, 'present' => $m['present']]
          );

          $values = Path::set($values, $m['path'], $value);
          $errors = array_merge($errors, $errs);
        }
        continue;
      }

      $p = Path::getWithPresence($input, $path);
      $raw = $p['present'] ? Path::get($source, $path) : null;

      [$value, $errs] = $field->process(
        $raw,
        $level,
        ['path' => $path, 'input' => $input, 'level' => $level, 'present' => $p['present']]
      );

      $values = Path::set($values, $path, $value);
      $errors = array_merge($errors, $errs);
    }

    if ($this->rejectUnknown) {
      $spec = $this->compileSpec();
      $errors = array_merge($errors, UnknownFieldDetector::detect($input, $spec));
    }

    foreach ($this->schemaValidators as $v) {
      $errors = array_merge($errors, $v->validate($values, ['input' => $input, 'level' => $level]));
    }

    return new Result($values, $errors, ['policyVersion' => $this->policyVersion]);
  }

  private function compileSpec(): SchemaSpecNode {
    $root = new SchemaSpecNode();

    foreach (array_keys($this->fields) as $path) {
      self::specAddPath($root, $path);
    }

    foreach ($this->objectSchemas as $path => $schema) {
      $node = self::specGetNodeForPath($root, $path);
      $node->mergeFrom($schema->compileSpec());
    }

    foreach ($this->eachObjectSchemas as $wildPath => $schema) {
      $node = self::specGetNodeForPath($root, $wildPath);
      $node->mergeFrom($schema->compileSpec());
    }

    return $root;
  }

  private static function specAddPath(SchemaSpecNode $root, string $path): void {
    $node = $root;
    foreach (Path::segments($path) as $seg) {
      if ($seg === '*') {
        $node = $node->wildcardChild();
      } else {
        $node = $node->child($seg);
      }
    }
  }

  private static function specGetNodeForPath(SchemaSpecNode $root, string $path): SchemaSpecNode {
    $node = $root;
    foreach (Path::segments($path) as $seg) {
      if ($seg === '*') {
        $node = $node->wildcardChild();
      } else {
        $node = $node->child($seg);
      }
    }
    return $node;
  }

  private function prefixError(string $prefix, Error $e): Error {
    $p = trim($prefix);
    $childPath = trim($e->path);

    $newPath = $childPath === '' ? $p : ($p === '' ? $childPath : $p . '.' . $childPath);

    return new Error($newPath, $e->code, $e->message, $e->meta);
  }

  public function disallowOverlaps(bool $enabled = true): self {
    $this->disallowOverlaps = $enabled;
    return $this;
  }

  private function hasOverlap(): bool {
  // Overlap if any field path is inside an object schema path or eachObject schema path
    foreach (array_keys($this->objectSchemas) as $objPath) {
      foreach (array_keys($this->fields) as $fieldPath) {
        if ($fieldPath === $objPath || str_starts_with($fieldPath, $objPath . '.')) {
          return true;
        }
      }
    }

    foreach (array_keys($this->eachObjectSchemas) as $wildObjPath) { // e.g. items.*
      $prefix = rtrim($wildObjPath, '.*');
      foreach (array_keys($this->fields) as $fieldPath) {
        // field like items.*.name overlaps eachObject items.*
        if ($fieldPath === $wildObjPath || str_starts_with($fieldPath, $prefix . '.*')) {
          return true;
        }
      }
    }

    return false;
  }

  public function rejectUnknownFields(bool $enabled = true): self {
    $this->rejectUnknown = $enabled;
    return $this;
  }

  public function rule(\InputGuard\Contract\SchemaValidator $validator): self {
    $this->schemaValidators[] = $validator;
    return $this;
  }

  public function policyVersion(string $v): self {
    $this->policyVersion = $v;
    return $this;
  }
}