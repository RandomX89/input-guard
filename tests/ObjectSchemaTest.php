<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class ObjectSchemaTest extends TestCase {
  public function test_nested_object_schema_prefixes_errors_and_values(): void {
    $userSchema = Schema::make()
      ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
      ->field('name', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));

    $schema = Schema::make()
      ->field('user', Type::object())     // type guard (optional but good)
      ->object('user', $userSchema);      // apply child schema

    $input = [
      'user' => [
        'email' => ' TEST@EXAMPLE.COM ',
        'name' => ' A '
      ]
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('user.name', $r->errors()[0]->path);
    $this->assertSame('min_len', $r->errors()[0]->code);

    $this->assertSame('test@example.com', $r->values()['user']['email']);
  }

  public function test_each_object_schema_validates_collection_of_objects(): void {
    $itemSchema = Schema::make()
      ->field('name', Type::string()->addValidate(Level::STRICT, [Val::required()]))
      ->field('qty', Type::int()->addValidate(Level::STRICT, [Val::min(1)]));

    $schema = Schema::make()
      ->eachObject('items', $itemSchema);

    $input = [
      'items' => [
        ['name' => 'ok', 'qty' => ' 2 '],
        ['name' => '   ', 'qty' => 1],
        ['name' => 'x', 'qty' => 0],
      ]
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertFalse($r->ok());

    // first failing element: items.1.name required
    $this->assertSame('items.1.name', $r->errors()[0]->path);

    // qty sanitized to int
    $this->assertSame(2, $r->values()['items'][0]['qty']);
  }
}