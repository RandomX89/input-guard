<?php
namespace InputGuard\Core;

final class Error {
    public function __construct(
        public readonly string $path,
        public readonly string $code,
        public readonly ?string $message = null,
        public readonly array $meta = []
    ) {}
}
