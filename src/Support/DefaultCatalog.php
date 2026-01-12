<?php
namespace RandomX98\InputGuard\Support;

use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class DefaultCatalog {
    public static function build(): MessageCatalog {
        $catalog = [
            'en' => [
                ErrorCode::REQUIRED => fn(Error $e) => 'Value is required',
                ErrorCode::STRING => fn(Error $e) => 'Expected a string',
                ErrorCode::INT => fn(Error $e) => 'Expected an integer',
                ErrorCode::EMAIL => fn(Error $e) => 'Invalid email address',
                ErrorCode::MIN_LEN => fn(Error $e) => 'String is too short',
                ErrorCode::MAX_LEN => fn(Error $e) => 'String is too long',
                ErrorCode::MIN => fn(Error $e) => 'Value is too small',
                ErrorCode::MAX => fn(Error $e) => 'Value is too large',
                ErrorCode::REGEX => fn(Error $e) => 'Invalid format',
                ErrorCode::IN_SET => fn(Error $e) => 'Value is not allowed',
                ErrorCode::NO_CONTROL_CHARS => fn(Error $e) => 'Control characters are not allowed',
                ErrorCode::ARRAY => fn(Error $e) => 'Expected an array',
                ErrorCode::MIN_ITEMS => fn(Error $e) => 'Array is too short',
                ErrorCode::MAX_ITEMS => fn(Error $e) => 'Array is too long',
            ],
            'it' => [
                ErrorCode::REQUIRED => fn(Error $e) => 'Campo obbligatorio',
                ErrorCode::STRING => fn(Error $e) => 'Deve essere una stringa',
                ErrorCode::INT => fn(Error $e) => 'Deve essere un numero intero',
                ErrorCode::EMAIL => fn(Error $e) => 'Email non valida',
                ErrorCode::MIN_LEN => fn(Error $e) => 'Testo troppo corto',
                ErrorCode::MAX_LEN => fn(Error $e) => 'Testo troppo lungo',
                ErrorCode::MIN => fn(Error $e) => 'Valore troppo piccolo',
                ErrorCode::MAX => fn(Error $e) => 'Valore troppo grande',
                ErrorCode::REGEX => fn(Error $e) => 'Formato non valido',
                ErrorCode::IN_SET => fn(Error $e) => 'Valore non consentito',
                ErrorCode::NO_CONTROL_CHARS => fn(Error $e) => 'Caratteri di controllo non consentiti',
                ErrorCode::ARRAY => fn(Error $e) => 'Deve essere un array',
                ErrorCode::MIN_ITEMS => fn(Error $e) => 'Array troppo corto',
                ErrorCode::MAX_ITEMS => fn(Error $e) => 'Array troppo lungo',
            ],
        ];

        // fallback finale “smart” con meta
        $fallback = function (Error $e, string $locale): string {
            $parts = [$e->code];
            if (isset($e->meta['min'])) $parts[] = "min={$e->meta['min']}";
            if (isset($e->meta['max'])) $parts[] = "max={$e->meta['max']}";
            return "{$e->path}: " . implode(' ', $parts);
        };

        return new MessageCatalog($catalog, 'en', $fallback);
    }
}