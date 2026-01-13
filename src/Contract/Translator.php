<?php
namespace InputGuard\Contract;

use InputGuard\Core\Error;

interface Translator {
    public function message(Error $error, string $locale = 'en'): string;
}