<?php
/**
 * Cross-Field Validation Example
 * 
 * Demonstrates schema-level validators for rules that span multiple fields.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use InputGuard\Contract\SchemaValidator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

/**
 * Validates that password matches password_confirm
 */
class PasswordConfirmValidator implements SchemaValidator {
    public function validate(array $values, array $context = []): array {
        $password = $values['password'] ?? null;
        $confirm = $values['password_confirm'] ?? null;

        if ($password !== $confirm) {
            return [new Error('password_confirm', ErrorCode::INVALID, null, ['rule' => 'password_mismatch'])];
        }
        return [];
    }
}

/**
 * Validates that end_date is after start_date
 */
class DateRangeValidator implements SchemaValidator {
    public function validate(array $values, array $context = []): array {
        $start = $values['start_date'] ?? null;
        $end = $values['end_date'] ?? null;

        if ($start && $end) {
            $startTs = strtotime($start);
            $endTs = strtotime($end);
            if ($endTs <= $startTs) {
                return [new Error('end_date', ErrorCode::INVALID, null, ['rule' => 'must_be_after_start'])];
            }
        }
        return [];
    }
}

// Registration form schema
$registrationSchema = Schema::make()
    ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
    ->field('password', Type::string()->addValidate(Level::STRICT, [
        Val::required(),
        Val::minLen(8),
    ]))
    ->field('password_confirm', Type::string()->addValidate(Level::STRICT, [Val::required()]))
    ->rule(new PasswordConfirmValidator());

echo "=== Password Confirmation ===\n\n";

// Passwords don't match
$result = $registrationSchema->process([
    'email' => 'user@example.com',
    'password' => 'secretpass',
    'password_confirm' => 'different',
], Level::STRICT);

echo "Mismatched passwords:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
foreach ($result->errors() as $error) {
    echo "  - {$error->path}: {$error->code} ({$error->meta['rule']})\n";
}

// Passwords match
$result = $registrationSchema->process([
    'email' => 'user@example.com',
    'password' => 'secretpass',
    'password_confirm' => 'secretpass',
], Level::STRICT);

echo "\nMatching passwords:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";

// Date range schema
$eventSchema = Schema::make()
    ->field('title', Type::string()->addValidate(Level::STRICT, [Val::required()]))
    ->field('start_date', Type::string()->addValidate(Level::STRICT, [Val::required()]))
    ->field('end_date', Type::string()->addValidate(Level::STRICT, [Val::required()]))
    ->rule(new DateRangeValidator());

echo "\n=== Date Range Validation ===\n\n";

// Invalid range
$result = $eventSchema->process([
    'title' => 'Conference',
    'start_date' => '2024-12-15',
    'end_date' => '2024-12-10', // before start!
], Level::STRICT);

echo "End before start:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
foreach ($result->errors() as $error) {
    echo "  - {$error->path}: {$error->code} ({$error->meta['rule']})\n";
}

// Valid range
$result = $eventSchema->process([
    'title' => 'Conference',
    'start_date' => '2024-12-10',
    'end_date' => '2024-12-15',
], Level::STRICT);

echo "\nValid date range:\n";
echo "OK: " . ($result->ok() ? 'true' : 'false') . "\n";
