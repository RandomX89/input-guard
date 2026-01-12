<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Core\Result;

final class Schema {
    /** @var array<string,Field> */
    private array $fields = [];

    public static function make(): self { return new self(); }

    public function field(string $name, Field $field): self {
        $this->fields[$name] = $field;
        return $this;
    }

    public function process(array $input, Level $level): Result {
        $values = [];
        $errors = [];

        foreach ($this->fields as $name => $field) {
            [$value, $errs] = $field->process(
                $input[$name] ?? null,
                $level,
                ['path' => $name, 'input' => $input]
            );

            $values[$name] = $value;
            $errors = array_merge($errors, $errs);
        }

        return new Result($values, $errors);
    }
}