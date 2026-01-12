<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

final class RejectUnknownFieldsTest extends TestCase {
  public function test_reject_unknown_fields_at_root(): void {
    $schema = Schema::make()
      ->field('a', Type::string())
      ->rejectUnknownFields();

    $r = $schema->process(['a' => 'ok', 'extra' => 'x'], Level::BASE);

    $this->assertFalse($r->ok());
    $this->assertSame('extra', $r->errors()[0]->path);
    $this->assertSame('unknown_field', $r->errors()[0]->code);
  }

  public function test_reject_unknown_fields_in_nested_object_schema(): void {
    $userSchema = Schema::make()
      ->field('email', Type::string());

    $schema = Schema::make()
      ->field('user', Type::object())
      ->object('user', $userSchema)
      ->rejectUnknownFields();

    $r = $schema->process(['user' => ['email' => 'ok', 'bad' => 'x']], Level::BASE);

    $this->assertFalse($r->ok());
    $this->assertSame('user.bad', $r->errors()[0]->path);
    $this->assertSame('unknown_field', $r->errors()[0]->code);
  }

  public function test_reject_unknown_fields_in_wildcard_elements(): void {
    $schema = Schema::make()
      ->field('items.*.name', Type::string())
      ->rejectUnknownFields();

    $r = $schema->process([
      'items' => [
        ['name' => 'ok', 'extra' => 'x'],
      ]
    ], Level::BASE);

    $this->assertFalse($r->ok());
    $this->assertSame('items.0.extra', $r->errors()[0]->path);
    $this->assertSame('unknown_field', $r->errors()[0]->code);
  }
}
