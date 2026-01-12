<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;

final class OverlapPolicyTest extends TestCase {
  public function test_default_policy_specific_field_wins(): void {
    $child = Schema::make()
      ->field('email', Type::email());

    $schema = Schema::make()
      ->object('user', $child)
      ->field('user.email', Type::email()->addValidate(Level::STRICT, [Val::required()]));

    $r = $schema->process(['user' => ['email' => ' TEST@EXAMPLE.COM ']], Level::STRICT);

    $this->assertTrue($r->ok());
    $this->assertSame('test@example.com', $r->values()['user']['email']);
  }

  public function test_strict_overlap_throws(): void {
    $this->expectException(\LogicException::class);

    $child = Schema::make()->field('email', Type::email());

    Schema::make()
      ->disallowOverlaps(true)
      ->object('user', $child)
      ->field('user.email', Type::email())
      ->process(['user' => ['email' => 'a@b.com']], Level::STRICT);
  }
}
