<?php
namespace InputGuard\Rules\San;

final class San {
    public static function trim(): TrimSanitizer { return new TrimSanitizer(); }
    public static function nullIfEmpty(): NullIfEmptySanitizer { return new NullIfEmptySanitizer(); }
    public static function lowercase(): LowercaseSanitizer { return new LowercaseSanitizer(); }
    public static function toInt(): ToIntSanitizer { return new ToIntSanitizer(); }
    public static function normalizeNfkc(): NormalizeNfkcSanitizer { return new NormalizeNfkcSanitizer(); }
    /** @param string[] $allowedTags */
    public static function stripTags(array $allowedTags = []): StripTagsSanitizer { return new StripTagsSanitizer($allowedTags); }
    public static function htmlEntities(int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, string $encoding = 'UTF-8'): HtmlEntitiesSanitizer { return new HtmlEntitiesSanitizer($flags, $encoding); }
}