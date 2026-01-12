<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

/**
 * Detects gibberish/nonsense text using heuristics:
 * - Too many consecutive consonants
 * - Abnormal vowel/consonant ratio
 * - Keyboard mashing patterns (qwerty, asdf)
 */
final class NoGibberishValidator implements Validator {
    private const VOWELS = 'aeiouAEIOUàèéìòùáéíóú';
    private const KEYBOARD_PATTERNS = [
        'qwert', 'asdf', 'zxcv', 'qazws', 'rewq', 'fdsa', 'vcxz',
        'yuiop', 'hjkl', 'bnm', 'poiuy', 'lkjh', 'mnb',
        '1234', '4321', 'abcd', 'dcba',
    ];

    public function __construct(
        private readonly int $maxConsecutiveConsonants = 5,
        private readonly float $minVowelRatio = 0.15,
        private readonly int $minLengthToCheck = 5
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || mb_strlen($value) < $this->minLengthToCheck) {
            return [];
        }

        $letters = preg_replace('/[^a-zA-ZàèéìòùáéíóúÀÈÉÌÒÙ]/u', '', $value);
        if (mb_strlen($letters) < $this->minLengthToCheck) {
            return [];
        }

        if ($this->hasExcessiveConsonants($letters)) {
            return [$this->error($context, 'excessive_consonants')];
        }

        if ($this->hasLowVowelRatio($letters)) {
            return [$this->error($context, 'low_vowel_ratio')];
        }

        $lower = mb_strtolower($value);
        if ($this->hasKeyboardPattern($lower)) {
            return [$this->error($context, 'keyboard_pattern')];
        }

        return [];
    }

    private function hasExcessiveConsonants(string $letters): bool {
        $consonantRun = 0;
        $maxRun = 0;

        for ($i = 0; $i < mb_strlen($letters); $i++) {
            $char = mb_substr($letters, $i, 1);
            if (mb_strpos(self::VOWELS, $char) === false) {
                $consonantRun++;
                $maxRun = max($maxRun, $consonantRun);
            } else {
                $consonantRun = 0;
            }
        }

        return $maxRun > $this->maxConsecutiveConsonants;
    }

    private function hasLowVowelRatio(string $letters): bool {
        $vowelCount = 0;
        $len = mb_strlen($letters);

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($letters, $i, 1);
            if (mb_strpos(self::VOWELS, $char) !== false) {
                $vowelCount++;
            }
        }

        return ($vowelCount / $len) < $this->minVowelRatio;
    }

    private function hasKeyboardPattern(string $text): bool {
        foreach (self::KEYBOARD_PATTERNS as $pattern) {
            if (str_contains($text, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function error(array $context, string $reason): Error {
        return new Error(
            $context['path'] ?? '',
            ErrorCode::GIBBERISH,
            null,
            ['reason' => $reason]
        );
    }
}
