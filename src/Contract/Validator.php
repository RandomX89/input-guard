<?php
namespace InputGuard\Contract;

use InputGuard\Core\Error;

interface Validator {
    /** @return Error[] */
    public function validate(mixed $value, array $context = []): array;
}