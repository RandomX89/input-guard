<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class FieldBehaviorTest extends TestCase {
    public function test_optional_skips_validation_on_empty_after_sanitize(): void {
        $schema = Schema::make()->field(
            'nickname',
            Type::string()
                ->optional()
                ->addValidate(Level::STRICT, [Val::minLen(3)]) // would fail if it ran
        );

        $r = $schema->process(['nickname' => '   '], Level::STRICT);

        $this->assertTrue($r->ok());
        $this->assertNull($r->values()['nickname']); // trim + nullIfEmpty? if your Type::string includes it
        // If your Type::string currently doesn't include nullIfEmpty at STRICT, then expected would be ''.
    }

    public function test_stop_on_first_error_stops_after_required(): void {
        $schema = Schema::make()->field(
            'name',
            Type::string()
                ->stopOnFirstError()
                ->addValidate(Level::STRICT, [Val::required(), Val::minLen(3)])
        );

        $r = $schema->process(['name' => ''], Level::STRICT);

        $this->assertFalse($r->ok());
        $this->assertCount(1, $r->errors());
        $this->assertSame('required', $r->errors()[0]->code);
    }

    public function test_regex_validator(): void {
        $schema = Schema::make()->field(
            'slug',
            Type::string()
                ->addValidate(Level::STRICT, [Val::regex('/^[a-z0-9-]+$/')])
        );

        $r = $schema->process(['slug' => 'NOPE!'], Level::STRICT);
        $this->assertFalse($r->ok());
        $this->assertSame('regex', $r->errors()[0]->code);
    }

    public function test_in_set_validator(): void {
        $schema = Schema::make()->field(
            'role',
            Type::string()
                ->addValidate(Level::STRICT, [Val::inSet(['admin', 'user'])])
        );

        $r = $schema->process(['role' => 'guest'], Level::STRICT);
        $this->assertFalse($r->ok());
        $this->assertSame('in_set', $r->errors()[0]->code);
    }
}