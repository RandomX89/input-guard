<?php
namespace RandomX98\InputGuard\Contract;

interface Sanitizer {
    public function apply(mixed $value, array $context = []): mixed;
}