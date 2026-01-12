<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Contract\Sanitizer;
use RandomX98\InputGuard\Contract\Validator;

final class Field {
    /** @var array<int,Sanitizer[]> */
    private array $sanitizersByLevel;
    /** @var array<int,Validator[]> */
    private array $validatorsByLevel;

    public function __construct(
        array $sanitizersByLevel = [],
        array $validatorsByLevel = []
    ) {
        $this->sanitizersByLevel = $sanitizersByLevel;
        $this->validatorsByLevel = $validatorsByLevel;
    }

    /** SET: replaces rules for that level */
    public function sanitize(Level $level, array $rules): self {
        $san = $this->sanitizersByLevel;
        $san[$level->value] = $this->assertSanitizers($rules);
        ksort($san);
        return new self($san, $this->validatorsByLevel);
    }

    /** SET: replaces rules for that level */
    public function validate(Level $level, array $rules): self {
        $val = $this->validatorsByLevel;
        $val[$level->value] = $this->assertValidators($rules);
        ksort($val);
        return new self($this->sanitizersByLevel, $val);
    }

    /** APPEND: adds rules after existing ones for that level */
    public function addSanitize(Level $level, array $rules): self {
        $san = $this->sanitizersByLevel;
        $existing = $san[$level->value] ?? [];
        $san[$level->value] = array_values(array_merge($existing, $this->assertSanitizers($rules)));
        ksort($san);
        return new self($san, $this->validatorsByLevel);
    }

    /** APPEND: adds rules after existing ones for that level */
    public function addValidate(Level $level, array $rules): self {
        $val = $this->validatorsByLevel;
        $existing = $val[$level->value] ?? [];
        $val[$level->value] = array_values(array_merge($existing, $this->assertValidators($rules)));
        ksort($val);
        return new self($this->sanitizersByLevel, $val);
    }

    /** @return array{0:mixed,1:array} */
    public function process(mixed $value, Level $level, array $context): array {
        foreach ($this->sanitizersByLevel as $lvl => $rules) {
            if ($lvl <= $level->value) {
                foreach ($rules as $rule) {
                    $value = $rule->apply($value, $context);
                }
            }
        }

        $errors = [];
        foreach ($this->validatorsByLevel as $lvl => $rules) {
            if ($lvl <= $level->value) {
                foreach ($rules as $rule) {
                    $errors = array_merge($errors, $rule->validate($value, $context));
                }
            }
        }

        return [$value, $errors];
    }

    /** @param array<int,mixed> $rules @return Sanitizer[] */
    private function assertSanitizers(array $rules): array {
        foreach ($rules as $r) {
            if (!$r instanceof Sanitizer) {
                throw new \InvalidArgumentException('All sanitize rules must implement Sanitizer');
            }
        }
        /** @var Sanitizer[] $rules */
        return array_values($rules);
    }

    /** @param array<int,mixed> $rules @return Validator[] */
    private function assertValidators(array $rules): array {
        foreach ($rules as $r) {
            if (!$r instanceof Validator) {
                throw new \InvalidArgumentException('All validate rules must implement Validator');
            }
        }
        /** @var Validator[] $rules */
        return array_values($rules);
    }
}