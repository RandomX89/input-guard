<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;
use InputGuard\Rules\Val\Val;

final class TypePresetTest extends TestCase {
    public function test_email_preset_lowercases_and_validates(): void {
        $schema = Schema::make()->field('email', Type::email()->validate(Level::STRICT, [Val::required()]));
        $r = $schema->process(['email' => ' TEST@EXAMPLE.COM '], Level::STRICT);

        $this->assertTrue($r->ok());
        $this->assertSame('test@example.com', $r->values()['email']);
    }

    public function test_int_preset_casts_numeric_string(): void {
        $schema = Schema::make()->field('age', Type::int()->validate(Level::STRICT, [Val::min(18)]));
        $r = $schema->process(['age' => ' 19 '], Level::STRICT);

        $this->assertTrue($r->ok());
        $this->assertSame(19, $r->values()['age']);
    }

    public function test_int_preset_rejects_non_int(): void {
        $schema = Schema::make()->field('age', Type::int());
        $r = $schema->process(['age' => '19.5'], Level::STRICT);

        $this->assertFalse($r->ok());
        $this->assertSame('int', $r->errors()[0]->code);
    }
}