<?php
namespace RandomX98\InputGuard\Rules\Val;

final class Val {
    public static function required(): RequiredValidator {
        return new RequiredValidator();
    }

    public static function noControlChars(): NoControlCharsValidator {
        return new NoControlCharsValidator();
    }
}