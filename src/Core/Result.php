<?php
namespace RandomX98\InputGuard\Core;

final class Result {
    /** @param array<string,mixed> $values */
    /** @param Error[] $errors */
    public function __construct(
        private readonly array $values,
        private readonly array $errors = []
    ) {}

    public function ok(): bool { return $this->errors === []; }

    /** @return array<string,mixed> */
    public function values(): array { return $this->values; }

    /** @return Error[] */
    public function errors(): array { return $this->errors; }
}
