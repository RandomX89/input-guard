<?php
namespace RandomX98\InputGuard\Contract;

use RandomX98\InputGuard\Core\Error;

interface Validator {
    /** @return Error[] */
    public function validate(mixed $value, array $context = []): array;
}