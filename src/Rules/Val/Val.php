<?php
namespace RandomX98\InputGuard\Rules\Val;

final class Val {
    public static function required(): RequiredValidator { return new RequiredValidator(); }
    public static function noControlChars(): NoControlCharsValidator { return new NoControlCharsValidator(); }

    public static function typeString(): TypeStringValidator { return new TypeStringValidator(); }
    public static function typeInt(): TypeIntValidator { return new TypeIntValidator(); }

    public static function maxLen(int $max): MaxLenValidator { return new MaxLenValidator($max); }
    public static function min(int $min): MinValidator { return new MinValidator($min); }
    public static function max(int $max): MaxValidator { return new MaxValidator($max); }

    public static function email(): EmailValidator { return new EmailValidator(); }
}