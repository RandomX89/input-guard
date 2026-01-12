<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;

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
