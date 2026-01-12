<?php
/**
 * Abuse Report Form Example
 * 
 * Demonstrates anti-spam validation for a public abuse report form.
 * Blocks bots, spammers, and nonsense submissions.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Support\DefaultCatalog;
use RandomX98\InputGuard\Support\PresentableErrors;

$schema = Schema::make()
    ->field('reporter_email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
    ->field('reported_url', RuleSet::paranoidUrl()->toField()->addValidate(Level::STRICT, [Val::required()]))
    ->field('abuse_type', Type::string()->addValidate(Level::STRICT, [
        Val::required(),
        Val::inSet(['spam', 'harassment', 'illegal_content', 'copyright', 'other']),
    ]))
    ->field('description', RuleSet::antiSpamStrict(10, 1000)->toField()->addValidate(Level::STRICT, [Val::required()]))
    ->field('website', RuleSet::honeypot()->toField())
    ->rejectUnknownFields();

echo "=== Abuse Report Form Validation ===\n\n";

// Test 1: Valid submission
echo "1. Valid submission:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'https://malicious-site.com/spam-page',
    'abuse_type' => 'spam',
    'description' => 'This website is sending unsolicited emails to my inbox. I have received over 50 spam messages from this domain in the last week. Please investigate and take action.',
    'website' => '', // honeypot empty (human)
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if ($result->ok()) {
    echo "   Email: {$result->values()['reporter_email']}\n";
    echo "   URL: {$result->values()['reported_url']}\n";
}

// Test 2: Bot submission (honeypot filled)
echo "\n2. Bot submission (honeypot filled):\n";
$result = $schema->process([
    'reporter_email' => 'bot@spam.com',
    'reported_url' => 'https://example.com',
    'abuse_type' => 'spam',
    'description' => 'This is a valid looking description with enough words.',
    'website' => 'http://bot-link.com', // honeypot filled (bot)
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code}\n";
}

// Test 3: Gibberish submission
echo "\n3. Gibberish submission:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'https://example.com',
    'abuse_type' => 'spam',
    'description' => 'asdfghjkl qwerty zxcvbnm poiuytrewq lkjhgfdsa mnbvcxz',
    'website' => '',
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code}\n";
}

// Test 4: ALL CAPS shouting
echo "\n4. ALL CAPS submission:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'https://example.com',
    'abuse_type' => 'harassment',
    'description' => 'THIS WEBSITE IS TERRIBLE AND I HATE IT SO MUCH PLEASE DELETE IT NOW!!!',
    'website' => '',
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code}\n";
}

// Test 5: Too short
echo "\n5. Too short submission:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'https://example.com',
    'abuse_type' => 'spam',
    'description' => 'Bad site.',
    'website' => '',
], Level::STRICT);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code}\n";
}

// Test 6: JavaScript URL attempt
echo "\n6. JavaScript URL injection:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'javascript:alert(document.cookie)',
    'abuse_type' => 'spam',
    'description' => 'This is a legitimate abuse report with enough words to pass validation.',
    'website' => '',
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code}\n";
}

// Test 7: Unknown field injection
echo "\n7. Unknown field injection:\n";
$result = $schema->process([
    'reporter_email' => 'user@example.com',
    'reported_url' => 'https://example.com',
    'abuse_type' => 'spam',
    'description' => 'Valid description with enough words to pass the minimum word count requirement.',
    'website' => '',
    'admin' => true, // unknown field!
], Level::PARANOID);

echo "   Status: " . ($result->ok() ? 'VALID' : 'REJECTED') . "\n";
if (!$result->ok()) {
    echo "   Reason: {$result->errors()[0]->code} at {$result->errors()[0]->path}\n";
}

echo "\n=== Translated Error Messages ===\n\n";

$translator = DefaultCatalog::build();

$result = $schema->process([
    'reporter_email' => '',
    'reported_url' => 'javascript:alert(1)',
    'abuse_type' => 'invalid_type',
    'description' => 'asdf',
    'website' => 'bot-filled',
], Level::PARANOID);

$errors = PresentableErrors::format($result->errors(), $translator, 'en');
echo "English:\n";
foreach ($errors as $err) {
    echo "  - {$err['path']}: {$err['message']}\n";
}

$errors = PresentableErrors::format($result->errors(), $translator, 'it');
echo "\nItalian:\n";
foreach ($errors as $err) {
    echo "  - {$err['path']}: {$err['message']}\n";
}
