<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Field;
use InputGuard\Rules\San\TrimSanitizer;
use InputGuard\Rules\Val\RequiredValidator;

final class SchemaSmokeTest extends TestCase {
    public function test_trim_then_required_fails_on_whitespace(): void {
        $schema = Schema::make()->field(
            'email',
            (new Field())
                ->sanitize(Level::BASE, [new TrimSanitizer()])
                ->validate(Level::STRICT, [new RequiredValidator()])
        );

        $result = $schema->process(['email' => '   '], Level::STRICT);

        $this->assertFalse($result->ok());
        $this->assertSame('', $result->values()['email']);
        $this->assertSame('required', $result->errors()[0]->code);
        $this->assertSame('email', $result->errors()[0]->path);
    }
}