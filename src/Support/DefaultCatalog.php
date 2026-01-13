<?php
namespace InputGuard\Support;

use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;

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
                ErrorCode::NO_ZERO_WIDTH_CHARS => fn(Error $e) => 'Zero-width characters are not allowed',
                ErrorCode::ARRAY => fn(Error $e) => 'Expected an array',
                ErrorCode::MIN_ITEMS => fn(Error $e) => 'Array is too short',
                ErrorCode::MAX_ITEMS => fn(Error $e) => 'Array is too long',
                ErrorCode::UNKNOWN_FIELD => fn(Error $e) => 'Unknown field',
                ErrorCode::NO_HTML_TAGS => fn(Error $e) => 'HTML tags are not allowed',
                ErrorCode::NO_SQL_PATTERNS => fn(Error $e) => 'Suspicious SQL pattern detected',
                ErrorCode::NO_PATH_TRAVERSAL => fn(Error $e) => 'Path traversal is not allowed',
                ErrorCode::NO_SHELL_CHARS => fn(Error $e) => 'Shell metacharacters are not allowed',
                ErrorCode::SAFE_URL => fn(Error $e) => 'URL is not safe',
                ErrorCode::SAFE_FILENAME => fn(Error $e) => 'Filename is not safe',
                ErrorCode::PRINTABLE_ONLY => fn(Error $e) => 'Only printable characters are allowed',
                ErrorCode::MAX_BYTES => fn(Error $e) => 'Value exceeds maximum byte size',
                ErrorCode::GIBBERISH => fn(Error $e) => 'Text appears to be gibberish',
                ErrorCode::EXCESSIVE_URLS => fn(Error $e) => 'Too many URLs in text',
                ErrorCode::REPEATED_CHARS => fn(Error $e) => 'Too many repeated characters',
                ErrorCode::SPAM_KEYWORDS => fn(Error $e) => 'Spam keywords detected',
                ErrorCode::MIN_WORDS => fn(Error $e) => 'Not enough words',
                ErrorCode::MAX_WORDS => fn(Error $e) => 'Too many words',
                ErrorCode::ALL_CAPS => fn(Error $e) => 'Text should not be all uppercase',
                ErrorCode::HONEYPOT => fn(Error $e) => 'Invalid submission',
                ErrorCode::SUSPICIOUS_PATTERN => fn(Error $e) => 'Suspicious input pattern detected',
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
                ErrorCode::NO_ZERO_WIDTH_CHARS => fn(Error $e) => 'Caratteri a larghezza zero non consentiti',
                ErrorCode::ARRAY => fn(Error $e) => 'Deve essere un array',
                ErrorCode::MIN_ITEMS => fn(Error $e) => 'Array troppo corto',
                ErrorCode::MAX_ITEMS => fn(Error $e) => 'Array troppo lungo',
                ErrorCode::UNKNOWN_FIELD => fn(Error $e) => 'Campo non consentito',
                ErrorCode::NO_HTML_TAGS => fn(Error $e) => 'I tag HTML non sono consentiti',
                ErrorCode::NO_SQL_PATTERNS => fn(Error $e) => 'Rilevato pattern SQL sospetto',
                ErrorCode::NO_PATH_TRAVERSAL => fn(Error $e) => 'Path traversal non consentito',
                ErrorCode::NO_SHELL_CHARS => fn(Error $e) => 'Metacaratteri shell non consentiti',
                ErrorCode::SAFE_URL => fn(Error $e) => 'URL non sicuro',
                ErrorCode::SAFE_FILENAME => fn(Error $e) => 'Nome file non sicuro',
                ErrorCode::PRINTABLE_ONLY => fn(Error $e) => 'Solo caratteri stampabili consentiti',
                ErrorCode::MAX_BYTES => fn(Error $e) => 'Valore supera la dimensione massima in byte',
                ErrorCode::GIBBERISH => fn(Error $e) => 'Il testo sembra essere senza senso',
                ErrorCode::EXCESSIVE_URLS => fn(Error $e) => 'Troppi URL nel testo',
                ErrorCode::REPEATED_CHARS => fn(Error $e) => 'Troppi caratteri ripetuti',
                ErrorCode::SPAM_KEYWORDS => fn(Error $e) => 'Rilevate parole chiave spam',
                ErrorCode::MIN_WORDS => fn(Error $e) => 'Parole insufficienti',
                ErrorCode::MAX_WORDS => fn(Error $e) => 'Troppe parole',
                ErrorCode::ALL_CAPS => fn(Error $e) => 'Il testo non deve essere tutto maiuscolo',
                ErrorCode::HONEYPOT => fn(Error $e) => 'Invio non valido',
                ErrorCode::SUSPICIOUS_PATTERN => fn(Error $e) => 'Rilevato pattern di input sospetto',
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