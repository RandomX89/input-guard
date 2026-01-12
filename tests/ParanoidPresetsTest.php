<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Schema;

final class ParanoidPresetsTest extends TestCase {
    public function test_paranoid_string_blocks_shell_chars_at_paranoid(): void {
        $schema = Schema::make()
            ->field('input', RuleSet::paranoidString()->toField());

        $r = $schema->process(['input' => 'test; rm -rf /'], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('no_shell_chars', $r->errors()[0]->code);
    }

    public function test_paranoid_string_blocks_path_traversal_at_paranoid(): void {
        $schema = Schema::make()
            ->field('input', RuleSet::paranoidString()->toField());

        $r = $schema->process(['input' => '../../../etc/passwd'], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('no_path_traversal', $r->errors()[0]->code);
    }

    public function test_paranoid_string_sanitizes_and_passes_clean_input(): void {
        $schema = Schema::make()
            ->field('input', RuleSet::paranoidString()->toField());

        $r = $schema->process(['input' => '  Hello World  '], Level::PSYCHOTIC);

        $this->assertTrue($r->ok());
        $this->assertSame('Hello World', $r->values()['input']);
    }

    public function test_paranoid_url_blocks_javascript(): void {
        $schema = Schema::make()
            ->field('link', RuleSet::paranoidUrl()->toField());

        $r = $schema->process(['link' => 'javascript:alert(1)'], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('safe_url', $r->errors()[0]->code);
    }

    public function test_paranoid_url_accepts_https(): void {
        $schema = Schema::make()
            ->field('link', RuleSet::paranoidUrl()->toField());

        $r = $schema->process(['link' => 'https://example.com'], Level::PARANOID);

        $this->assertTrue($r->ok());
    }

    public function test_paranoid_filename_blocks_php(): void {
        $schema = Schema::make()
            ->field('file', RuleSet::paranoidFilename()->toField());

        $r = $schema->process(['file' => 'shell.php'], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('safe_filename', $r->errors()[0]->code);
    }

    public function test_paranoid_filename_blocks_path_traversal(): void {
        $schema = Schema::make()
            ->field('file', RuleSet::paranoidFilename()->toField());

        $r = $schema->process(['file' => '../../../etc/passwd'], Level::PARANOID);

        $this->assertFalse($r->ok());
        $this->assertSame('no_path_traversal', $r->errors()[0]->code);
    }

    public function test_paranoid_filename_accepts_safe_name(): void {
        $schema = Schema::make()
            ->field('file', RuleSet::paranoidFilename()->toField());

        $r = $schema->process(['file' => 'document-2024.pdf'], Level::PARANOID);

        $this->assertTrue($r->ok());
    }
}
