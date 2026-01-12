<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class WildcardPathsTest extends TestCase {
  public function test_wildcard_nested_path_sanitizes_each_item(): void {
    $schema = Schema::make()
      ->field('items.*.name', Type::string()->addValidate(Level::STRICT, [Val::required()]));

    $input = [
      'items' => [
        ['name' => '  Alice  '],
        ['name' => ' Bob '],
      ]
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertTrue($r->ok());
    $this->assertSame('Alice', $r->values()['items'][0]['name']);
    $this->assertSame('Bob', $r->values()['items'][1]['name']);
  }

  public function test_wildcard_collects_indexed_errors(): void {
    $schema = Schema::make()
      ->field('items.*.name', Type::string()->addValidate(Level::STRICT, [Val::required()])->stopOnFirstError());

    $input = [
      'items' => [
        ['name' => 'ok'],
        ['name' => '   '], // becomes null/'' depending on Type::string preset
        ['name' => 'yo'],
      ]
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('items.1.name', $r->errors()[0]->path);
    $this->assertSame('required', $r->errors()[0]->code);
  }

  public function test_each_applies_to_array_elements(): void {
    $schema = Schema::make()
      ->each('tags', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));

    $input = [
      'tags' => [' ok ', 'x', ' nice ']
    ];

    $r = $schema->process($input, Level::STRICT);

    $this->assertFalse($r->ok());
    $this->assertSame('tags.1', $r->errors()[0]->path);
    $this->assertSame('min_len', $r->errors()[0]->code);

    // Sanitization still applied for others
    $this->assertSame('ok', $r->values()['tags'][0]);
    $this->assertSame('nice', $r->values()['tags'][2]);
  }
}