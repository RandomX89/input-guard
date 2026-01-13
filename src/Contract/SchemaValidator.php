<?php
namespace InputGuard\Contract;

use InputGuard\Core\Error;

interface SchemaValidator {
  /** @return Error[] */
  public function validate(array $values, array $context = []): array;
}