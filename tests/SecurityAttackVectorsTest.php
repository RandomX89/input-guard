<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\RuleSet;
use InputGuard\Schema\Schema;

final class SecurityAttackVectorsTest extends TestCase {
    public function test_no_path_traversal_blocks_windows_drive_paths(): void {
        $v = Val::noPathTraversal();

        $errs = $v->validate('C:\\Windows\\system.ini', ['path' => 'file']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_path_traversal', $errs[0]->code);

        $errs = $v->validate('C:/Windows/system.ini', ['path' => 'file']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_path_traversal', $errs[0]->code);
    }

    public function test_no_path_traversal_blocks_encoded_dot_dot(): void {
        $v = Val::noPathTraversal();
        $errs = $v->validate('%2E%2E%2Fetc/passwd', ['path' => 'file']);

        $this->assertCount(1, $errs);
        $this->assertSame('no_path_traversal', $errs[0]->code);
    }

    public function test_no_sql_patterns_blocks_or_equals_bypass(): void {
        $v = Val::noSqlPatterns();
        $errs = $v->validate("' OR 1=1 --", ['path' => 'q']);

        $this->assertCount(1, $errs);
        $this->assertSame('no_sql_patterns', $errs[0]->code);
    }

    public function test_safe_url_blocks_obfuscated_javascript_scheme(): void {
        $v = Val::safeUrl();
        $errs = $v->validate("  JaVaScRiPt:alert(1)", ['path' => 'link']);

        $this->assertCount(1, $errs);
        $this->assertSame('safe_url', $errs[0]->code);
    }

    public function test_no_shell_chars_blocks_dollar_paren(): void {
        $v = Val::noShellChars();
        $errs = $v->validate('$(whoami)', ['path' => 'cmd']);

        $this->assertCount(1, $errs);
        $this->assertSame('no_shell_chars', $errs[0]->code);
    }

    public function test_paranoid_string_blocks_null_bytes(): void {
        $schema = Schema::make()
            ->field('comment', RuleSet::paranoidString()->toField());

        $r = $schema->process(['comment' => "hello\x00world"], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('no_control_chars', $r->errors()[0]->code);
    }

    public function test_paranoid_string_blocks_zero_width_obfuscation(): void {
        $schema = Schema::make()
            ->field('comment', RuleSet::paranoidString()->toField());

        $r = $schema->process(['comment' => "safe\u{200D}word"], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('no_zero_width_chars', $r->errors()[0]->code);
    }

    public function test_paranoid_string_blocks_sql_injection_at_psychotic(): void {
        $schema = Schema::make()
            ->field('comment', RuleSet::paranoidString()->toField());

        $r = $schema->process(['comment' => "name' OR 1=1 --"], Level::PSYCHOTIC);

        $this->assertFalse($r->ok());
        $this->assertSame('no_sql_patterns', $r->errors()[0]->code);
    }
}
