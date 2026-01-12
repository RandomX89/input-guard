<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class RegexValidator implements Validator {
    public function __construct(
        private readonly string $pattern,
        private readonly bool $allowEmpty = true
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if ($value === null) return [];
        if (!is_string($value)) return [new Error($context['path'] ?? '', ErrorCode::REGEX, null, ['pattern' => $this->pattern, 'value' => $value])];

        if ($value === '' && $this->allowEmpty) return [];

        $ok = @preg_match($this->pattern, $value);
        if ($ok !== 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::REGEX,
                null,
                ['pattern' => $this->pattern, 'value' => $value]
            )];
        }

        return [];
    }
}