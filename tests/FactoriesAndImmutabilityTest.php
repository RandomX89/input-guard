<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\Field;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Rules\San\San;
use RandomX98\InputGuard\Rules\Val\Val;

final class FactoriesAndImmutabilityTest extends TestCase {
    public function test_field_is_immutable(): void {
        $base = new Field();
        $a = $base->sanitize(Level::BASE, [San::trim()]);
        $b = $base->validate(Level::STRICT, [Val::required()]);

        $this->assertNotSame($base, $a);
        $this->assertNotSame($base, $b);
    }

    public function test_no_control_chars_validator(): void {
        $schema = Schema::make()->field(
            'name',
            (new Field())
                ->sanitize(Level::BASE, [San::trim()])
                ->validate(Level::PARANOID, [Val::noControlChars()])
        );

        $result = $schema->process(['name' => "A\x01B"], Level::PARANOID);
        $this->assertFalse($result->ok());
        $this->assertSame('no_control_chars', $result->errors()[0]->code);
    }
}