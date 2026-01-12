<?php
namespace RandomX98\InputGuard\Contract;

use RandomX98\InputGuard\Core\Error;

interface SchemaValidator {
  /** @return Error[] */
  public function validate(array $values, array $context = []): array;
}