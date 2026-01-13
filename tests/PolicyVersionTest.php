<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;

final class PolicyVersionTest extends TestCase {
  public function test_result_contains_policy_version_meta(): void {
    $schema = Schema::make()->policyVersion('1.2.3');

    $r = $schema->process([], Level::BASE);

    $this->assertSame('1.2.3', $r->meta()['policyVersion'] ?? null);
  }
}
