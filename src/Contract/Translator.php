<?php
namespace RandomX98\InputGuard\Contract;

use RandomX98\InputGuard\Core\Error;

interface Translator {
    public function message(Error $error, string $locale = 'en'): string;
}