<?php
/**
 * Error Translations Example
 * 
 * Demonstrates how to translate validation errors for display.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;
use RandomX98\InputGuard\Support\DefaultCatalog;
use RandomX98\InputGuard\Support\PresentableErrors;

$schema = Schema::make()
    ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
    ->field('age', Type::int()->addValidate(Level::STRICT, [Val::min(18)]))
    ->field('comment', Type::string()->addValidate(Level::PARANOID, [Val::noHtmlTags()]));

$result = $schema->process([
    'email' => '',
    'age' => 10,
    'comment' => '<script>alert(1)</script>',
], Level::PARANOID);

$translator = DefaultCatalog::build();

echo "=== English Messages ===\n\n";
$enErrors = PresentableErrors::format($result->errors(), $translator, 'en');
foreach ($enErrors as $error) {
    echo "  {$error['path']}: {$error['message']}\n";
}

echo "\n=== Italian Messages ===\n\n";
$itErrors = PresentableErrors::format($result->errors(), $translator, 'it');
foreach ($itErrors as $error) {
    echo "  {$error['path']}: {$error['message']}\n";
}

echo "\n=== JSON API Response ===\n\n";
echo json_encode([
    'success' => false,
    'errors' => $enErrors,
], JSON_PRETTY_PRINT) . "\n";
