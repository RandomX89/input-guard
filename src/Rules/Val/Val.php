<?php
namespace InputGuard\Rules\Val;

final class Val {
    public static function required(): RequiredValidator { return new RequiredValidator(); }
    public static function noControlChars(): NoControlCharsValidator { return new NoControlCharsValidator(); }
    public static function noZeroWidthChars(): NoZeroWidthCharsValidator { return new NoZeroWidthCharsValidator(); }
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

    // Security validators
    public static function noHtmlTags(): NoHtmlTagsValidator { return new NoHtmlTagsValidator(); }
    public static function noSqlPatterns(): NoSqlPatternsValidator { return new NoSqlPatternsValidator(); }
    public static function noPathTraversal(): NoPathTraversalValidator { return new NoPathTraversalValidator(); }
    public static function noShellChars(): NoShellCharsValidator { return new NoShellCharsValidator(); }
    /** @param string[] $allowedSchemes */
    public static function safeUrl(array $allowedSchemes = ['http', 'https']): SafeUrlValidator { return new SafeUrlValidator($allowedSchemes); }
    public static function safeFilename(bool $blockDangerousExtensions = true): SafeFilenameValidator { return new SafeFilenameValidator($blockDangerousExtensions); }
    public static function printableOnly(bool $allowNewlines = true, bool $allowTabs = true): PrintableOnlyValidator { return new PrintableOnlyValidator($allowNewlines, $allowTabs); }
    public static function maxBytes(int $max): MaxBytesValidator { return new MaxBytesValidator($max); }

    // Anti-spam/bot validators
    public static function noGibberish(int $maxConsecutiveConsonants = 5, float $minVowelRatio = 0.15): NoGibberishValidator { return new NoGibberishValidator($maxConsecutiveConsonants, $minVowelRatio); }
    public static function noExcessiveUrls(int $maxUrls = 2): NoExcessiveUrlsValidator { return new NoExcessiveUrlsValidator($maxUrls); }
    public static function noRepeatedChars(int $maxRepeats = 4): NoRepeatedCharsValidator { return new NoRepeatedCharsValidator($maxRepeats); }
    /** @param string[] $additionalPatterns */
    public static function noSpamKeywords(array $additionalPatterns = [], bool $useDefaults = true): NoSpamKeywordsValidator { return new NoSpamKeywordsValidator($additionalPatterns, $useDefaults); }
    public static function minWords(int $min): MinWordCountValidator { return new MinWordCountValidator($min); }
    public static function maxWords(int $max): MaxWordCountValidator { return new MaxWordCountValidator($max); }
    public static function noAllCaps(float $maxUppercaseRatio = 0.7): NoAllCapsValidator { return new NoAllCapsValidator($maxUppercaseRatio); }
    public static function honeypot(): HoneypotValidator { return new HoneypotValidator(); }
    public static function noSuspiciousPattern(float $maxPunctuationRatio = 0.3, float $maxDigitRatio = 0.4): NoSuspiciousPatternValidator { return new NoSuspiciousPatternValidator($maxPunctuationRatio, $maxDigitRatio); }
}