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
    public static function minLen(int $min): MinLenValidator { return new MinLenValidator($min); }
    public static function regex(string $pattern, bool $allowEmpty = true): RegexValidator { return new RegexValidator($pattern, $allowEmpty); }
    /** @param array<int,string|int> $allowed */
    public static function inSet(array $allowed, bool $strict = true): InSetValidator { return new InSetValidator($allowed, $strict); }
    public static function typeArray(): TypeArrayValidator { return new TypeArrayValidator(); }
    public static function minItems(int $min): MinItemsValidator { return new MinItemsValidator($min); }
    public static function maxItems(int $max): MaxItemsValidator { return new MaxItemsValidator($max); }
    public static function typeObject(): TypeObjectValidator { return new TypeObjectValidator(); }
}