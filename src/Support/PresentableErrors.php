<?php
namespace InputGuard\Support;

use InputGuard\Contract\Translator;
use InputGuard\Core\Error;

final class PresentableErrors {
    /**
     * @param Error[] $errors
     * @return array<int,array{path:string,code:string,message:string,meta:array}>
     */
    public static function format(array $errors, Translator $translator, string $locale = 'en'): array {
        return array_map(function (Error $e) use ($translator, $locale) {
            return [
                'path' => $e->path,
                'code' => $e->code,
                'message' => $translator->message($e, $locale),
                'meta' => $e->meta,
            ];
        }, $errors);
    }
}