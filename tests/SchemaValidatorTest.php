<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Contract\SchemaValidator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;
use InputGuard\Rules\Val\Val;

final class SchemaValidatorTest extends TestCase {
  public function test_schema_rule_can_validate_cross_fields(): void {
    $schema = Schema::make()
      ->field('password', Type::string()->addValidate(Level::STRICT, [Val::required()]))
      ->field('password_confirm', Type::string()->addValidate(Level::STRICT, [Val::required()]))
      ->rule(new class implements SchemaValidator {
        public function validate(array $values, array $context = []): array {
          $p = $values['password'] ?? null;
          $c = $values['password_confirm'] ?? null;

          if ($p !== $c) {
            return [new Error('password_confirm', ErrorCode::INVALID, null, ['rule' => 'password_confirm'])];
          }

          return [];
        }
      });

    $r = $schema->process([
      'password' => 'secret',
      'password_confirm' => 'different'
    ], Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('password_confirm', $r->errors()[0]->path);
    $this->assertSame('invalid', $r->errors()[0]->code);
  }

  public function test_schema_level_validator_receives_sanitized_values(): void {
    $receivedValues = null;

    $schema = Schema::make()
      ->field('email', Type::email())
      ->rule(new class($receivedValues) implements SchemaValidator {
        private mixed $ref;

        public function __construct(mixed &$ref) {
          $this->ref = &$ref;
        }

        public function validate(array $values, array $context = []): array {
          $this->ref = $values;
          return [];
        }
      });

    $schema->process(['email' => ' TEST@EXAMPLE.COM '], Level::STRICT);

    $this->assertSame('test@example.com', $receivedValues['email'] ?? null);
  }
}
