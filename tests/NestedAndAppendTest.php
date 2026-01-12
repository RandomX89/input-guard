<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class NestedAndAppendTest extends TestCase {
    public function test_nested_path_is_read_and_written(): void {
        $schema = Schema::make()
            ->field('user.email', Type::email()->addValidate(Level::STRICT, [Val::required()]));

        $r = $schema->process(['user' => ['email' => ' TEST@EXAMPLE.COM ']], Level::STRICT);

        $this->assertTrue($r->ok());
        $this->assertSame('test@example.com', $r->values()['user']['email']);
    }

    public function test_add_validate_appends_in_order(): void {
        $field = Type::string()
            ->addValidate(Level::STRICT, [Val::required()])
            ->addValidate(Level::STRICT, [Val::maxLen(3)]);

        $schema = Schema::make()->field('name', $field);
        $r = $schema->process(['name' => 'abcd'], Level::STRICT);

        $this->assertFalse($r->ok());
        // required passes, maxLen fails
        $this->assertSame('max_len', $r->errors()[0]->code);
    }
}