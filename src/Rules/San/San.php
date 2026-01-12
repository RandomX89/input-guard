<?php
namespace RandomX98\InputGuard\Rules\San;

final class San {
    public static function trim(): TrimSanitizer { return new TrimSanitizer(); }
    public static function nullIfEmpty(): NullIfEmptySanitizer { return new NullIfEmptySanitizer(); }
    public static function lowercase(): LowercaseSanitizer { return new LowercaseSanitizer(); }
    public static function toInt(): ToIntSanitizer { return new ToIntSanitizer(); }
}