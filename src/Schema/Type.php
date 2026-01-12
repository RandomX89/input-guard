<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\San\San;
use RandomX98\InputGuard\Rules\Val\Val;

final class Type {
    public static function string(): Field {
        // BASE: normalize
        // PARANOID+: hardening
        // STRICT+: typical constraints live at call-site (required, maxLen, etc.)
        return (new Field())
            ->sanitize(Level::BASE, [San::trim()])
            ->sanitize(Level::STRICT, [San::nullIfEmpty()])
            ->validate(Level::BASE, [Val::typeString()])
            ->validate(Level::PARANOID, [Val::noControlChars()]);
    }

    public static function email(): Field {
        return self::string()
            ->sanitize(Level::STRICT, [San::lowercase()])
            ->validate(Level::STRICT, [Val::email()])
            ->validate(Level::PSYCHOTIC, [Val::maxLen(254)]);
    }

    public static function int(): Field {
        return (new Field())
            ->sanitize(Level::BASE, [San::trim(), San::nullIfEmpty(), San::toInt()])
            ->validate(Level::BASE, [Val::typeInt()]);
    }

    public static function array(): Field {
        return (new Field())
            ->validate(Level::BASE, [Val::typeArray()]);
    }

    public static function arrayOf(Field $elementField): ArrayOf {
        return new ArrayOf(self::array(), $elementField);
    }
}