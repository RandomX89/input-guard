<?php
namespace RandomX98\InputGuard\Rules\San;

final class San {
    public static function trim(): TrimSanitizer {
        return new TrimSanitizer();
    }
}