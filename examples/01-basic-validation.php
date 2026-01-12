<?php
/**
 * Basic Validation Example
 * 
 * Demonstrates simple field validation with types and levels.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

$schema = Schema::make()
    ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
    ->field('age', Type::int()->addValidate(Level::STRICT, [Val::min(18), Val::max(120)]))
    ->field('username', Type::string()->addValidate(Level::STRICT, [
        Val::required(),
        Val::minLen(3),
        Val::maxLen(30),
        Val::regex('/^[a-zA-Z0-9._-]+$/'),
    ]));

// Valid input
$result = $schema->process([
    'email' => '  USER@EXAMPLE.COM  ',
    'age' => '25',
    'username' => '  john_doe  ',
], Level::STRICT);

echo "Valid input:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
echo "Email: " . $result->values()['email'] . "\n";
echo "Age: " . $result->values()['age'] . "\n";
echo "Username: " . $result->values()['username'] . "\n\n";

// Invalid input
$result = $schema->process([
    'email' => 'not-an-email',
    'age' => 10,
    'username' => 'ab',
], Level::STRICT);

echo "Invalid input:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
echo "Errors:\n";
foreach ($result->errors() as $error) {
    echo "  - {$error->path}: {$error->code}\n";
}
