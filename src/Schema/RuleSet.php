<?php
namespace RandomX98\InputGuard\Schema;

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Contract\Sanitizer;
use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Rules\San\San;
use RandomX98\InputGuard\Rules\Val\Val;

final class RuleSet {
    /** @var array<int,Sanitizer[]> */
    private array $sanByLevel;
    /** @var array<int,Validator[]> */
    private array $valByLevel;

    private bool $optional;
    private bool $stopOnFirstError;

    public function __construct(
        array $sanByLevel = [],
        array $valByLevel = [],
        bool $optional = false,
        bool $stopOnFirstError = false
    ) {
        $this->sanByLevel = $sanByLevel;
        $this->valByLevel = $valByLevel;
        $this->optional = $optional;
        $this->stopOnFirstError = $stopOnFirstError;
    }

    public static function make(): self {
        return new self();
    }

    /** @param Sanitizer[] $rules */
    public function sanitize(Level $level, array $rules): self {
        $san = $this->sanByLevel;
        $san[$level->value] = $this->assertSanitizers($rules);
        ksort($san);
        return new self($san, $this->valByLevel, $this->optional, $this->stopOnFirstError);
    }

    /** @param Sanitizer[] $rules */
    public function addSanitize(Level $level, array $rules): self {
        $san = $this->sanByLevel;
        $existing = $san[$level->value] ?? [];
        $san[$level->value] = array_values(array_merge($existing, $this->assertSanitizers($rules)));
        ksort($san);
        return new self($san, $this->valByLevel, $this->optional, $this->stopOnFirstError);
    }

    /** @param Validator[] $rules */
    public function validate(Level $level, array $rules): self {
        $val = $this->valByLevel;
        $val[$level->value] = $this->assertValidators($rules);
        ksort($val);
        return new self($this->sanByLevel, $val, $this->optional, $this->stopOnFirstError);
    }

    /** @param Validator[] $rules */
    public function addValidate(Level $level, array $rules): self {
        $val = $this->valByLevel;
        $existing = $val[$level->value] ?? [];
        $val[$level->value] = array_values(array_merge($existing, $this->assertValidators($rules)));
        ksort($val);
        return new self($this->sanByLevel, $val, $this->optional, $this->stopOnFirstError);
    }

    public function optional(bool $enabled = true): self {
        return new self($this->sanByLevel, $this->valByLevel, $enabled, $this->stopOnFirstError);
    }

    public function stopOnFirstError(bool $enabled = true): self {
        return new self($this->sanByLevel, $this->valByLevel, $this->optional, $enabled);
    }

    /**
     * Merge: l'ordine è importante.
     * - Sanitizers/validators vengono APPESI per livello (A poi B).
     * - Flags: se uno dei due è true, diventa true.
     */
    public function merge(RuleSet $other): self {
        $san = $this->sanByLevel;
        foreach ($other->sanByLevel as $lvl => $rules) {
            $san[$lvl] = array_values(array_merge($san[$lvl] ?? [], $rules));
        }
        ksort($san);

        $val = $this->valByLevel;
        foreach ($other->valByLevel as $lvl => $rules) {
            $val[$lvl] = array_values(array_merge($val[$lvl] ?? [], $rules));
        }
        ksort($val);

        return new self(
            $san,
            $val,
            $this->optional || $other->optional,
            $this->stopOnFirstError || $other->stopOnFirstError
        );
    }

    /** Applica il RuleSet a un Field esistente (append). */
    public function applyTo(Field $field): Field {
        foreach ($this->sanByLevel as $lvl => $rules) {
            $field = $field->addSanitize(Level::from($lvl), $rules);
        }
        foreach ($this->valByLevel as $lvl => $rules) {
            $field = $field->addValidate(Level::from($lvl), $rules);
        }

        if ($this->optional) {
            $field = $field->optional(true);
        }
        if ($this->stopOnFirstError) {
            $field = $field->stopOnFirstError(true);
        }

        return $field;
    }

    /** Crea un Field nuovo a partire solo dal RuleSet. */
    public function toField(): Field {
        return $this->applyTo(new Field());
    }

    // --------------------
    // Preset “di base”
    // --------------------

    /** Username pragmatico: trim + nullIfEmpty + no control chars + regex + min/max len. */
    public static function username(int $min = 3, int $max = 30): self {
        return self::make()
            ->sanitize(Level::BASE, [San::trim()])
            ->sanitize(Level::STRICT, [San::nullIfEmpty()])
            ->validate(Level::BASE, [Val::typeString()])
            ->validate(Level::PARANOID, [Val::noControlChars()])
            ->validate(Level::STRICT, [
                Val::minLen($min),
                Val::maxLen($max),
                Val::regex('/^[a-zA-Z0-9._-]+$/', allowEmpty: false),
            ]);
    }

    /** Slug: lowercase + regex semplice + max len. */
    public static function slug(int $max = 80): self {
        return self::make()
            ->sanitize(Level::BASE, [San::trim()])
            ->sanitize(Level::STRICT, [San::nullIfEmpty(), San::lowercase()])
            ->validate(Level::BASE, [Val::typeString()])
            ->validate(Level::PARANOID, [Val::noControlChars()])
            ->validate(Level::STRICT, [
                Val::maxLen($max),
                Val::regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', allowEmpty: false),
            ]);
    }

    /** Email preset “solo regole” (se vuoi usarlo con o senza Type::email()). */
    public static function email(): self {
        return self::make()
            ->sanitize(Level::BASE, [San::trim()])
            ->sanitize(Level::STRICT, [San::nullIfEmpty(), San::lowercase()])
            ->validate(Level::BASE, [Val::typeString()])
            ->validate(Level::PARANOID, [Val::noControlChars()])
            ->validate(Level::STRICT, [Val::email()])
            ->validate(Level::PSYCHOTIC, [Val::maxLen(254)]);
    }

    // --------------------
    // Assertions
    // --------------------
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