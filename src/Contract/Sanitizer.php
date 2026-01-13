<?php
namespace InputGuard\Contract;

interface Sanitizer {
    public function apply(mixed $value, array $context = []): mixed;
}