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

    /** @param Sanitizer[] $rules */
    public function sanitize(Level $level, array $rules): self {
        $san = $this->sanitizersByLevel;
        $san[$level->value] = $rules;
        ksort($san);
        return new self($san, $this->validatorsByLevel);
    }

    /** @param Validator[] $rules */
    public function validate(Level $level, array $rules): self {
        $val = $this->validatorsByLevel;
        $val[$level->value] = $rules;
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
}