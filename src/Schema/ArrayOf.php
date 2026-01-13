<?php
namespace InputGuard\Schema;

use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;

final class ArrayOf {
  public function __construct(
    private Field $arrayField,
    private Field $elementField
  ) {}

  public function minItems(int $min, Level $level = Level::STRICT): self {
    return new self(
      $this->arrayField->addValidate($level, [Val::minItems($min)]),
      $this->elementField
    );
  }

  public function maxItems(int $max, Level $level = Level::STRICT): self {
    return new self(
      $this->arrayField->addValidate($level, [Val::maxItems($max)]),
      $this->elementField
    );
  }

  public function optional(bool $enabled = true): self {
    return new self($this->arrayField->optional($enabled), $this->elementField);
  }

  public function stopOnFirstError(bool $enabled = true): self {
    return new self($this->arrayField->stopOnFirstError($enabled), $this->elementField);
  }

  public function arrayField(): Field { return $this->arrayField; }
  public function elementField(): Field { return $this->elementField; }

  /** Applica a uno schema: valida l’array e ogni elemento. */
  public function applyTo(Schema $schema, string $path): Schema {
    return $schema
      ->field($path, $this->arrayField)
      ->each($path, $this->elementField);
  }
}