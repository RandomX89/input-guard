<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Contract\Sanitizer;
use RandomX98\InputGuard\Contract\Validator;

final class Field {
    /** @var array<int,Sanitizer[]> */
    private array $sanitizersByLevel = [];
    /** @var array<int,Validator[]> */
    private array $validatorsByLevel = [];

    /** @param Sanitizer[] $rules */
    public function sanitize(Level $level, array $rules): self {
        $this->sanitizersByLevel[$level->value] = $rules;
        ksort($this->sanitizersByLevel);
        return $this;
    }

    /** @param Validator[] $rules */
    public function validate(Level $level, array $rules): self {
        $this->validatorsByLevel[$level->value] = $rules;
        ksort($this->validatorsByLevel);
        return $this;
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
}