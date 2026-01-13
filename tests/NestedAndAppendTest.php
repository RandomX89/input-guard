<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;
use InputGuard\Rules\Val\Val;

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