<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Contract\Sanitizer;
use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Schema\RuleSet;

final class Field {
    /** @var array<int,Sanitizer[]> */
    private array $sanitizersByLevel;
    /** @var array<int,Validator[]> */
    private array $validatorsByLevel;

    private bool $isOptional;
    private bool $stopOnFirstError;

    public function __construct(
        array $sanitizersByLevel = [],
        array $validatorsByLevel = [],
        bool $isOptional = false,
        bool $stopOnFirstError = false
    ) {
        $this->sanitizersByLevel = $sanitizersByLevel;
        $this->validatorsByLevel = $validatorsByLevel;
        $this->isOptional = $isOptional;
        $this->stopOnFirstError = $stopOnFirstError;
    }

    public function optional(bool $enabled = true): self {
        return new self($this->sanitizersByLevel, $this->validatorsByLevel, $enabled, $this->stopOnFirstError);
    }

    public function stopOnFirstError(bool $enabled = true): self {
        return new self($this->sanitizersByLevel, $this->validatorsByLevel, $this->isOptional, $enabled);
    }

    /** SET: replaces rules for that level */
    public function sanitize(Level $level, array $rules): self {
        $san = $this->sanitizersByLevel;
        $san[$level->value] = $this->assertSanitizers($rules);
        ksort($san);
        return new self($san, $this->validatorsByLevel, $this->isOptional, $this->stopOnFirstError);
    }

    /** SET: replaces rules for that level */
    public function validate(Level $level, array $rules): self {
        $val = $this->validatorsByLevel;
        $val[$level->value] = $this->assertValidators($rules);
        ksort($val);
        return new self($this->sanitizersByLevel, $val, $this->isOptional, $this->stopOnFirstError);
    }

    /** APPEND: adds rules after existing ones for that level */
    public function addSanitize(Level $level, array $rules): self {
        $san = $this->sanitizersByLevel;
        $existing = $san[$level->value] ?? [];
        $san[$level->value] = array_values(array_merge($existing, $this->assertSanitizers($rules)));
        ksort($san);
        return new self($san, $this->validatorsByLevel, $this->isOptional, $this->stopOnFirstError);
    }

    /** APPEND: adds rules after existing ones for that level */
    public function addValidate(Level $level, array $rules): self {
        $val = $this->validatorsByLevel;
        $existing = $val[$level->value] ?? [];
        $val[$level->value] = array_values(array_merge($existing, $this->assertValidators($rules)));
        ksort($val);
        return new self($this->sanitizersByLevel, $val, $this->isOptional, $this->stopOnFirstError);
    }

    /** @return array{0:mixed,1:array} */
    public function process(mixed $value, Level $level, array $context): array {
        // 1) sanitize
        foreach ($this->sanitizersByLevel as $lvl => $rules) {
            if ($lvl <= $level->value) {
                foreach ($rules as $rule) {
                    $value = $rule->apply($value, $context);
                }
            }
        }

        // 2) optional short-circuit (after sanitization)
        if ($this->isOptional && ($value === null || (is_string($value) && $value === ''))) {
            return [$value, []];
        }

        // 3) validate
        $errors = [];
        foreach ($this->validatorsByLevel as $lvl => $rules) {
            if ($lvl <= $level->value) {
                foreach ($rules as $rule) {
                    $newErrors = $rule->validate($value, $context);
                    if ($newErrors !== []) {
                        $errors = array_merge($errors, $newErrors);
                        if ($this->stopOnFirstError) {
                            return [$value, $errors];
                        }
                    }
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

    public function use(RuleSet $set): self {
        return $set->applyTo($this);
    }
}