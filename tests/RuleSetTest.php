<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Rules\Val\Val;

final class RuleSetTest extends TestCase {
    public function test_ruleset_applies_to_field_and_validates(): void {
        $schema = Schema::make()->field(
            'user.username',
            Type::string()
                ->use(RuleSet::username())
                ->addValidate(Level::STRICT, [Val::required()])
                ->stopOnFirstError()
        );

        $r = $schema->process(['user' => ['username' => '  ab ']], Level::STRICT);
        $this->assertFalse($r->ok());
        $this->assertSame('min_len', $r->errors()[0]->code);
    }

    public function test_ruleset_merge_appends_in_order(): void {
        $a = RuleSet::make()->addValidate(Level::STRICT, [Val::minLen(3)]);
        $b = RuleSet::make()->addValidate(Level::STRICT, [Val::maxLen(5)]);
        $merged = $a->merge($b);

        $schema = Schema::make()->field('x', $merged->toField());
        $r = $schema->process(['x' => 'abcdef'], Level::STRICT);

        $this->assertFalse($r->ok());
        $this->assertSame('max_len', $r->errors()[0]->code);
    }

    public function test_ruleset_optional_flag_is_applied(): void {
        $schema = Schema::make()->field(
            'nick',
            RuleSet::make()
                ->optional()
                ->addValidate(Level::STRICT, [Val::minLen(3)])
                ->toField()
        );

        $r = $schema->process(['nick' => ''], Level::STRICT);
        $this->assertTrue($r->ok());
    }
}