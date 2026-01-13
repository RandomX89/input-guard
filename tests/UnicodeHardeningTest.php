<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Rules\San\San;
use InputGuard\Rules\Val\Val;

final class UnicodeHardeningTest extends TestCase {
  public function test_normalize_nfkc_is_safe_without_intl(): void {
    $san = San::normalizeNfkc();

    $in = "Ａ"; // FULLWIDTH LATIN CAPITAL LETTER A
    $out = $san->apply($in);

    if (class_exists(\Normalizer::class)) {
      $this->assertSame('A', $out);
    } else {
      $this->assertSame($in, $out);
    }
  }

  public function test_no_zero_width_chars_validator(): void {
    $v = Val::noZeroWidthChars();

    $errs = $v->validate("ab\u{200B}cd", ['path' => 'field']);

    $this->assertCount(1, $errs);
    $this->assertSame('field', $errs[0]->path);
    $this->assertSame('no_zero_width_chars', $errs[0]->code);
  }
}
