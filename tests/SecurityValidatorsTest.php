<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Rules\San\San;
use InputGuard\Rules\Val\Val;

final class SecurityValidatorsTest extends TestCase {
    // ==================== SANITIZERS ====================

    public function test_strip_tags_removes_html(): void {
        $san = San::stripTags();
        $this->assertSame('Hello World', $san->apply('<p>Hello <b>World</b></p>'));
    }

    public function test_strip_tags_allows_specified_tags(): void {
        $san = San::stripTags(['b', 'i']);
        $this->assertSame('Hello <b>World</b>', $san->apply('<p>Hello <b>World</b></p>'));
    }

    public function test_html_entities_encodes_special_chars(): void {
        $san = San::htmlEntities();
        $result = $san->apply('<script>alert("xss")</script>');
        $this->assertStringContainsString('&lt;', $result);
        $this->assertStringContainsString('&gt;', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    // ==================== VALIDATORS ====================

    public function test_no_html_tags_fails_on_html(): void {
        $v = Val::noHtmlTags();
        $errs = $v->validate('<script>alert(1)</script>', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_html_tags', $errs[0]->code);
    }

    public function test_no_html_tags_passes_clean_text(): void {
        $v = Val::noHtmlTags();
        $errs = $v->validate('Hello World 123', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_sql_patterns_detects_union_select(): void {
        $v = Val::noSqlPatterns();
        $errs = $v->validate("' UNION SELECT * FROM users --", ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_sql_patterns', $errs[0]->code);
    }

    public function test_no_sql_patterns_detects_drop_table(): void {
        $v = Val::noSqlPatterns();
        $errs = $v->validate("'; DROP TABLE users; --", ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_sql_patterns', $errs[0]->code);
    }

    public function test_no_sql_patterns_passes_normal_text(): void {
        $v = Val::noSqlPatterns();
        $errs = $v->validate('My name is John and I like SQL tutorials', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_path_traversal_detects_dot_dot_slash(): void {
        $v = Val::noPathTraversal();
        $errs = $v->validate('../../../etc/passwd', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_path_traversal', $errs[0]->code);
    }

    public function test_no_path_traversal_detects_absolute_path(): void {
        $v = Val::noPathTraversal();
        $errs = $v->validate('/etc/passwd', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_path_traversal', $errs[0]->code);
    }

    public function test_no_path_traversal_passes_safe_path(): void {
        $v = Val::noPathTraversal();
        $errs = $v->validate('uploads/image.jpg', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_shell_chars_detects_semicolon(): void {
        $v = Val::noShellChars();
        $errs = $v->validate('ls; rm -rf /', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_shell_chars', $errs[0]->code);
    }

    public function test_no_shell_chars_detects_pipe(): void {
        $v = Val::noShellChars();
        $errs = $v->validate('cat file | grep password', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_shell_chars', $errs[0]->code);
    }

    public function test_no_shell_chars_detects_backtick(): void {
        $v = Val::noShellChars();
        $errs = $v->validate('echo `whoami`', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('no_shell_chars', $errs[0]->code);
    }

    public function test_no_shell_chars_passes_safe_text(): void {
        $v = Val::noShellChars();
        $errs = $v->validate('Hello World 123', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_safe_url_rejects_javascript(): void {
        $v = Val::safeUrl();
        $errs = $v->validate('javascript:alert(1)', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('safe_url', $errs[0]->code);
    }

    public function test_safe_url_rejects_data_uri(): void {
        $v = Val::safeUrl();
        $errs = $v->validate('data:text/html,<script>alert(1)</script>', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('safe_url', $errs[0]->code);
    }

    public function test_safe_url_accepts_https(): void {
        $v = Val::safeUrl();
        $errs = $v->validate('https://example.com/path?query=1', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_safe_url_rejects_ftp_by_default(): void {
        $v = Val::safeUrl();
        $errs = $v->validate('ftp://example.com/file.txt', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('safe_url', $errs[0]->code);
    }

    public function test_safe_url_allows_custom_schemes(): void {
        $v = Val::safeUrl(['http', 'https', 'ftp']);
        $errs = $v->validate('ftp://example.com/file.txt', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_safe_filename_rejects_path_chars(): void {
        $v = Val::safeFilename();
        $errs = $v->validate('file/name.txt', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('safe_filename', $errs[0]->code);
    }

    public function test_safe_filename_rejects_php_extension(): void {
        $v = Val::safeFilename();
        $errs = $v->validate('shell.php', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('safe_filename', $errs[0]->code);
    }

    public function test_safe_filename_accepts_safe_names(): void {
        $v = Val::safeFilename();
        $errs = $v->validate('document-2024.pdf', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_printable_only_rejects_non_printable(): void {
        $v = Val::printableOnly();
        $errs = $v->validate("hello\x00world", ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('printable_only', $errs[0]->code);
    }

    public function test_printable_only_allows_newlines_by_default(): void {
        $v = Val::printableOnly();
        $errs = $v->validate("hello\nworld", ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_printable_only_can_reject_newlines(): void {
        $v = Val::printableOnly(allowNewlines: false);
        $errs = $v->validate("hello\nworld", ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('printable_only', $errs[0]->code);
    }

    public function test_max_bytes_rejects_oversized(): void {
        $v = Val::maxBytes(10);
        $errs = $v->validate('This is a very long string', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('max_bytes', $errs[0]->code);
    }

    public function test_max_bytes_accepts_within_limit(): void {
        $v = Val::maxBytes(100);
        $errs = $v->validate('Short text', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_max_bytes_counts_utf8_correctly(): void {
        $v = Val::maxBytes(10);
        $errs = $v->validate('日本語', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }
}
