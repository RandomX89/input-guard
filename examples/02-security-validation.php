<?php
/**
 * Security Validation Example
 * 
 * Demonstrates paranoid presets for maximum security against malicious input.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Schema;

$schema = Schema::make()
    ->field('comment', RuleSet::paranoidString()->toField())
    ->field('website', RuleSet::paranoidUrl()->toField())
    ->field('filename', RuleSet::paranoidFilename()->toField());

echo "=== Testing ParanoidString ===\n\n";

// XSS attempt
$result = $schema->process([
    'comment' => '<script>alert("xss")</script>',
    'website' => 'https://example.com',
    'filename' => 'document.pdf',
], Level::PARANOID);
echo "XSS attempt: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if (!$result->ok()) {
    echo "  Error: {$result->errors()[0]->path} -> {$result->errors()[0]->code}\n";
}

// Shell injection attempt
$result = $schema->process([
    'comment' => 'test; rm -rf /',
    'website' => 'https://example.com',
    'filename' => 'document.pdf',
], Level::PARANOID);
echo "Shell injection: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if (!$result->ok()) {
    echo "  Error: {$result->errors()[0]->path} -> {$result->errors()[0]->code}\n";
}

// Path traversal attempt
$result = $schema->process([
    'comment' => 'Hello World',
    'website' => 'https://example.com',
    'filename' => '../../../etc/passwd',
], Level::PARANOID);
echo "Path traversal: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if (!$result->ok()) {
    echo "  Error: {$result->errors()[0]->path} -> {$result->errors()[0]->code}\n";
}

echo "\n=== Testing ParanoidUrl ===\n\n";

// JavaScript URL attempt
$result = $schema->process([
    'comment' => 'Hello',
    'website' => 'javascript:alert(1)',
    'filename' => 'doc.pdf',
], Level::PARANOID);
echo "JavaScript URL: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if (!$result->ok()) {
    echo "  Error: {$result->errors()[0]->path} -> {$result->errors()[0]->code}\n";
}

echo "\n=== Testing ParanoidFilename ===\n\n";

// PHP file upload attempt
$result = $schema->process([
    'comment' => 'Hello',
    'website' => 'https://example.com',
    'filename' => 'shell.php',
], Level::PARANOID);
echo "PHP upload: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if (!$result->ok()) {
    echo "  Error: {$result->errors()[0]->path} -> {$result->errors()[0]->code}\n";
}

echo "\n=== Clean input at PSYCHOTIC level ===\n\n";

$result = $schema->process([
    'comment' => '  This is a clean comment.  ',
    'website' => '  https://example.com  ',
    'filename' => '  document-2024.pdf  ',
], Level::PSYCHOTIC);
echo "Clean input: " . ($result->ok() ? 'PASSED' : 'BLOCKED') . "\n";
if ($result->ok()) {
    echo "  comment: '{$result->values()['comment']}'\n";
    echo "  website: '{$result->values()['website']}'\n";
    echo "  filename: '{$result->values()['filename']}'\n";
}
