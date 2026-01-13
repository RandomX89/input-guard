<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;

final class MissingVsNullTest extends TestCase {
  public function test_required_fails_when_missing(): void {
    $schema = Schema::make()
      ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]));

    $r = $schema->process([], Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('email', $r->errors()[0]->path);
    $this->assertSame('required', $r->errors()[0]->code);
  }

  public function test_required_fails_when_wildcard_leaf_missing(): void {
    $schema = Schema::make()
      ->field('items.*.name', Type::string()->addValidate(Level::STRICT, [Val::required()]));

    $input = [
      'items' => [
        ['name' => 'ok'],
        []
      ]
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('items.1.name', $r->errors()[0]->path);
    $this->assertSame('required', $r->errors()[0]->code);
  }
}
