<?php
namespace InputGuard\Support;

use InputGuard\Contract\Translator;
use InputGuard\Core\Error;

final class MessageCatalog implements Translator {
    /**
     * @param array<string,array<string,callable>> $catalog
     *  Esempio:
     *  [
     *    'en' => [
     *      'required' => fn(Error $e) => 'Value is required',
     *      'min_len'  => fn(Error $e) => "Minimum length is {$e->meta['min']}",
     *    ],
     *    'it' => [...]
     *  ]
     */
    public function __construct(
        private array $catalog,
        private string $fallbackLocale = 'en',
        private ?\Closure $fallbackFormatter = null
    ) {}

    public function message(Error $error, string $locale = 'en'): string {
        // 1) usa già presente (se un consumer/validator vuole forzare un messaggio)
        if (is_string($error->message) && $error->message !== '') {
            return $error->message;
        }

        // 2) cerca in locale richiesto
        $msg = $this->resolve($locale, $error);
        if ($msg !== null) return $msg;

        // 3) fallback locale
        if ($locale !== $this->fallbackLocale) {
            $msg = $this->resolve($this->fallbackLocale, $error);
            if ($msg !== null) return $msg;
        }

        // 4) fallback finale
        if ($this->fallbackFormatter) {
            return ($this->fallbackFormatter)($error, $locale);
        }

        // default minimale (non bello, ma stabile)
        return "{$error->path}:{$error->code}";
    }

    private function resolve(string $locale, Error $error): ?string {
        $byLocale = $this->catalog[$locale] ?? null;
        if (!is_array($byLocale)) return null;

        $fn = $byLocale[$error->code] ?? null;
        if (!is_callable($fn)) return null;

        return (string)$fn($error);
    }
}