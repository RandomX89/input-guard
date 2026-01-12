<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Core\Result;
use RandomX98\InputGuard\Support\Path;

final class Schema {
    /** @var array<string,Field> */
    private array $fields = [];

    public static function make(): self { return new self(); }

    public function field(string $path, Field $field): self {
        $this->fields[$path] = $field;
        return $this;
    }

    public function process(array $input, Level $level): Result {
        $values = [];
        $errors = [];

        foreach ($this->fields as $path => $field) {
            $raw = Path::get($input, $path);

            [$value, $errs] = $field->process(
                $raw,
                $level,
                [
                    'path' => $path,
                    'input' => $input,
                    'level' => $level,
                ]
            );

            $values = Path::set($values, $path, $value);
            $errors = array_merge($errors, $errs);
        }

        return new Result($values, $errors);
    }
}