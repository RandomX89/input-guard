<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class ArrayOfTest extends TestCase {
  public function test_min_items_validator(): void {
    $schema = Schema::make()
      ->field('tags', Type::array()->addValidate(Level::STRICT, [Val::minItems(2)]));

    $r = $schema->process(['tags' => ['one']], Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('min_items', $r->errors()[0]->code);
    $this->assertSame('tags', $r->errors()[0]->path);
  }

  public function test_array_of_applies_array_and_each(): void {
    $schema = Schema::make();
    $schema = Type::arrayOf(Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]))
      ->minItems(2)
      ->applyTo($schema, 'tags');

    $r = $schema->process(['tags' => [' ok ', 'x', ' nice ']], Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('tags.1', $r->errors()[0]->path);
    $this->assertSame('min_len', $r->errors()[0]->code);

    $this->assertSame('ok', $r->values()['tags'][0]);
    $this->assertSame('nice', $r->values()['tags'][2]);
  }

  public function test_type_array_error(): void {
    $schema = Schema::make()
      ->field('tags', Type::array());

    $r = $schema->process(['tags' => 'nope'], Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('array', $r->errors()[0]->code);
    $this->assertSame('tags', $r->errors()[0]->path);
  }
}