<?php
/**
 * Nested Objects Example
 * 
 * Demonstrates nested object schemas and array of objects.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

// Define user schema
$userSchema = Schema::make()
    ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
    ->field('name', Type::string()->addValidate(Level::STRICT, [Val::required(), Val::minLen(2)]));

// Define item schema
$itemSchema = Schema::make()
    ->field('name', Type::string()->addValidate(Level::STRICT, [Val::required()]))
    ->field('qty', Type::int()->addValidate(Level::STRICT, [Val::required(), Val::min(1)]));

// Main schema with nested objects
$schema = Schema::make()
    ->field('user', Type::object())
    ->object('user', $userSchema)
    ->field('items', Type::array()->addValidate(Level::STRICT, [Val::minItems(1)]))
    ->eachObject('items', $itemSchema)
    ->rejectUnknownFields();

echo "=== Valid Order ===\n\n";

$result = $schema->process([
    'user' => [
        'email' => '  CUSTOMER@EXAMPLE.COM  ',
        'name' => '  John Doe  ',
    ],
    'items' => [
        ['name' => '  Widget  ', 'qty' => 2],
        ['name' => '  Gadget  ', 'qty' => 1],
    ],
], Level::STRICT);

echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
if ($result->ok()) {
    $values = $result->values();
    echo "User email: {$values['user']['email']}\n";
    echo "User name: {$values['user']['name']}\n";
    echo "Items:\n";
    foreach ($values['items'] as $i => $item) {
        echo "  {$i}: {$item['name']} x {$item['qty']}\n";
    }
}

echo "\n=== Invalid Order (validation errors) ===\n\n";

$result = $schema->process([
    'user' => [
        'email' => 'not-valid',
        'name' => 'J',
    ],
    'items' => [
        ['name' => 'Widget', 'qty' => 0],
        ['qty' => 5], // missing name
    ],
], Level::STRICT);

echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
echo "Errors:\n";
foreach ($result->errors() as $error) {
    echo "  - {$error->path}: {$error->code}\n";
}

echo "\n=== Unknown fields rejected ===\n\n";

$result = $schema->process([
    'user' => [
        'email' => 'user@example.com',
        'name' => 'John',
        'admin' => true, // unknown field!
    ],
    'items' => [
        ['name' => 'Widget', 'qty' => 1, 'discount' => 10], // unknown field!
    ],
], Level::STRICT);

echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
echo "Errors:\n";
foreach ($result->errors() as $error) {
    echo "  - {$error->path}: {$error->code}\n";
}
