# InputGuard

**InputGuard** is a **personal, opinionated PHP library** for **input sanitization and validation**, developed primarily for internal use and experimentation.

It focuses on **clarity, determinism, and security-oriented design**, rather than being a drop-in replacement for full-featured validation frameworks.

The library provides:

- **Deterministic pipelines**  
  Sanitization is always applied before validation, in a predictable and explicit order.

- **Configurable severity levels**  
  (`BASE`, `STRICT`, `PARANOID`, `PSYCHOTIC`) to progressively harden validation rules depending on context and risk tolerance.

- **Nested paths with dot notation**  
  (`user.email`) and **wildcards** (`items.*.name`) for complex and deeply structured inputs.

- **Composable presets** via `Type` and `RuleSet`  
  Designed to express intent clearly and reduce repetition in validation logic.

- **Localization-ready error handling**  
  Validators emit stable error codes and metadata only — user-facing messages are resolved externally.

- **Security-first mindset**  
  Includes defensive measures against common classes of unsafe input (such as XSS vectors, control characters, unsafe paths, and command injection patterns), while remaining explicit and opt-in.

## Table of contents

- [Installation](#installation)
- [Core concepts](#core-concepts)
- [Levels](#levels)
- [Quick start](#quick-start)
- [Types](#types)
- [RuleSet presets](#ruleset-presets)
- [Security presets](#security-presets)
- [Sanitizers reference](#sanitizers-reference)
- [Validators reference](#validators-reference)
- [Optional and stopOnFirstError](#optional-and-stoponfirsterror)
- [Nested paths and wildcards](#nested-paths-and-wildcards)
- [Arrays: each, arrayOf, minItems/maxItems](#arrays-each-arrayof-minitemsmaxitems)
- [Objects: object and eachObject](#objects-object-and-eachobject)
- [Reject unknown fields](#reject-unknown-fields)
- [Schema-level validators](#schema-level-validators)
- [Policy versioning](#policy-versioning)
- [Errors and translations](#errors-and-translations)
- [Extending](#extending)
- [Testing](#testing)

## Installation

This package is a private library. Add it as a VCS repository and require it.

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:RandomX98/input-guard.git"
    }
  ],
  "require": {
    "randomx98/input-guard": "dev-main"
  }
}
```

```bash
composer install
```

Requirements:

- PHP `>= 8.1`
- `ext-mbstring`

## Core concepts

- **Schema**
  Describes the whole input structure (request body, form, JSON payload).
- **Field**
  A pipeline of sanitizers and validators, configurable by `Level`.
- **Type**
  A reusable `Field` preset for a data shape (string, email, int, array, object).
- **RuleSet**
  A reusable set of rules you can apply to a `Field` (e.g. username, slug, email).
- **Error**
  Structured validation issue: `path`, `code`, `message` (optional), `meta` (array).
- **Translator**
  Converts an `Error` into a localized message (`MessageCatalog` / `DefaultCatalog`).

## Levels

Levels are cumulative: requesting `PSYCHOTIC` applies `BASE`, `STRICT`, `PARANOID`, and `PSYCHOTIC`.

- `Level::BASE`
  Safe normalization and type guards.
- `Level::STRICT`
  Typical business constraints.
- `Level::PARANOID`
  Defensive hardening.
- `Level::PSYCHOTIC`
  Extreme restrictions.

## Quick start

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make()
  ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
  ->field('age', Type::int()->addValidate(Level::STRICT, [Val::min(18)]));

$result = $schema->process($_POST, Level::STRICT);

if (!$result->ok()) {
  // $result->errors() contains Error objects
}

$clean = $result->values();
```

## Types

Types return a `Field` preset.

Available:

- `Type::string()`
- `Type::email()`
- `Type::int()`
- `Type::array()`
- `Type::object()`
- `Type::arrayOf(Field $elementField)` (returns an `ArrayOf` helper)

Example:

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Type;

$field = Type::string()
  ->addValidate(Level::STRICT, [Val::minLen(3)]);
```

## RuleSet presets

RuleSets are composable rule bundles.

Built-in presets:

- `RuleSet::username(int $min = 3, int $max = 30)`
- `RuleSet::slug(int $max = 80)`
- `RuleSet::email()`
- `RuleSet::paranoidString(int $maxLen = 1000, int $maxBytes = 4000)` - Maximum security for untrusted input
- `RuleSet::paranoidUrl(array $allowedSchemes = ['http', 'https'])` - Secure URL validation
- `RuleSet::paranoidFilename(int $maxLen = 255)` - Secure filename validation
- `RuleSet::antiSpam(int $minWords = 3, int $maxWords = 500)` - Anti-bot/spam heuristics
- `RuleSet::antiSpamStrict()` - Anti-spam + security validators combined
- `RuleSet::honeypot()` - Hidden field trap for bots

Apply a RuleSet to a Field:

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\RuleSet;
use InputGuard\Schema\Type;

$field = Type::string()
  ->use(RuleSet::username())
  ->addValidate(Level::STRICT, [Val::required()]);
```

Merge RuleSets (order matters: A then B):

```php
use InputGuard\Schema\RuleSet;

$rules = RuleSet::username()->merge(RuleSet::slug());
```

## Security presets

For maximum protection against malicious input, use the paranoid presets:

### ParanoidString

Protects against XSS, SQL injection, path traversal, shell injection, and more:

```php
use InputGuard\Core\Level;
use InputGuard\Schema\RuleSet;
use InputGuard\Schema\Schema;

$schema = Schema::make()
    ->field('comment', RuleSet::paranoidString()->toField());

$result = $schema->process(['comment' => '<script>alert(1)</script>'], Level::PARANOID);
// Fails with 'no_html_tags' error
```

**Protection by level:**

| Level | Sanitizers | Validators |
|-------|------------|------------|
| BASE | `trim`, `normalizeNfkc` | `typeString` |
| STRICT | `nullIfEmpty` | `maxLen` |
| PARANOID | `stripTags` | `noControlChars`, `noZeroWidthChars`, `noHtmlTags`, `noPathTraversal`, `noShellChars` |
| PSYCHOTIC | - | `noSqlPatterns`, `printableOnly`, `maxBytes` |

### ParanoidUrl

Secure URL validation blocking `javascript:`, `data:`, `vbscript:` schemes:

```php
$schema = Schema::make()
    ->field('website', RuleSet::paranoidUrl()->toField());

$result = $schema->process(['website' => 'javascript:alert(1)'], Level::PARANOID);
// Fails with 'safe_url' error
```

### ParanoidFilename

Secure filename validation for file uploads:

```php
$schema = Schema::make()
    ->field('upload', RuleSet::paranoidFilename()->toField());

$result = $schema->process(['upload' => 'shell.php'], Level::PARANOID);
// Fails with 'safe_filename' error
```

## Sanitizers reference

All sanitizers are available via `San::*` factory methods.

| Method | Description |
|--------|-------------|
| `San::trim()` | Trims whitespace from both ends |
| `San::nullIfEmpty()` | Converts empty strings to `null` |
| `San::lowercase()` | Converts to lowercase |
| `San::toInt()` | Casts value to integer |
| `San::normalizeNfkc()` | NFKC Unicode normalization (requires ext-intl, safe no-op otherwise) |
| `San::stripTags(array $allowed = [])` | Removes HTML tags (optionally allow specific tags) |
| `San::htmlEntities()` | Encodes HTML special characters |

## Validators reference

All validators are available via `Val::*` factory methods.

### Type validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::typeString()` | `string` | Value must be a string |
| `Val::typeInt()` | `int` | Value must be an integer |
| `Val::typeArray()` | `array` | Value must be an array |
| `Val::typeObject()` | `object` | Value must be an associative array |

### String validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::required()` | `required` | Value is required (not null/empty) |
| `Val::minLen(int $min)` | `min_len` | Minimum string length |
| `Val::maxLen(int $max)` | `max_len` | Maximum string length |
| `Val::email()` | `email` | Valid email format |
| `Val::regex(string $pattern)` | `regex` | Matches regex pattern |
| `Val::inSet(array $allowed)` | `in_set` | Value in allowed set |

### Numeric validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::min(int $min)` | `min` | Minimum value |
| `Val::max(int $max)` | `max` | Maximum value |

### Array validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::minItems(int $min)` | `min_items` | Minimum array items |
| `Val::maxItems(int $max)` | `max_items` | Maximum array items |

### Security validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::noControlChars()` | `no_control_chars` | Blocks control characters (0x00-0x1F, 0x7F) |
| `Val::noZeroWidthChars()` | `no_zero_width_chars` | Blocks zero-width Unicode characters |
| `Val::noHtmlTags()` | `no_html_tags` | Blocks HTML tags |
| `Val::noSqlPatterns()` | `no_sql_patterns` | Blocks SQL injection patterns |
| `Val::noPathTraversal()` | `no_path_traversal` | Blocks `../`, absolute paths, null bytes |
| `Val::noShellChars()` | `no_shell_chars` | Blocks shell metacharacters (`;`, `|`, `&`, etc.) |
| `Val::safeUrl(array $schemes)` | `safe_url` | Blocks dangerous URL schemes |
| `Val::safeFilename()` | `safe_filename` | Validates safe filename characters and extensions |
| `Val::printableOnly()` | `printable_only` | Only printable ASCII characters |
| `Val::maxBytes(int $max)` | `max_bytes` | Maximum byte size (not character count) |

### Anti-spam/bot validators

| Method | Error code | Description |
|--------|------------|-------------|
| `Val::noGibberish()` | `gibberish` | Detects keyboard mashing, excessive consonants, low vowel ratio |
| `Val::noExcessiveUrls(int $max)` | `excessive_urls` | Blocks text with too many URLs |
| `Val::noRepeatedChars(int $max)` | `repeated_chars` | Blocks excessive character repetition (aaaa, !!!!) |
| `Val::noSpamKeywords()` | `spam_keywords` | Blocks common spam phrases |
| `Val::minWords(int $min)` | `min_words` | Minimum word count |
| `Val::maxWords(int $max)` | `max_words` | Maximum word count |
| `Val::noAllCaps()` | `all_caps` | Blocks ALL CAPS text (shouting) |
| `Val::honeypot()` | `honeypot` | Field must be empty (bot trap) |
| `Val::noSuspiciousPattern()` | `suspicious_pattern` | Detects excessive punctuation, digits, etc. |

## Anti-spam presets

For public-facing forms like comments, contact forms, and abuse reports:

### AntiSpam

Basic heuristic validation to catch bots and spammers:

```php
use InputGuard\Core\Level;
use InputGuard\Schema\RuleSet;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make()
    ->field('message', RuleSet::antiSpam()->toField())
    ->field('email', Type::email());

$result = $schema->process([
    'message' => 'asdfghjkl qwerty',  // Gibberish!
    'email' => 'user@example.com',
], Level::PARANOID);

// Fails with 'gibberish' error
```

**Protection by level:**

| Level | Validators |
|-------|------------|
| STRICT | `minWords`, `maxWords`, `noRepeatedChars`, `noExcessiveUrls` |
| PARANOID | `noGibberish`, `noAllCaps`, `noSuspiciousPattern` |
| PSYCHOTIC | `noSpamKeywords` |

### AntiSpamStrict

Combines anti-spam heuristics with security validators:

```php
$schema = Schema::make()
    ->field('message', RuleSet::antiSpamStrict()->toField());
```

### Honeypot

Hidden field trap for bots (add a hidden field that should remain empty):

```php
$schema = Schema::make()
    ->field('email', Type::email())
    ->field('message', RuleSet::antiSpam()->toField())
    ->field('website', RuleSet::honeypot()->toField());  // Hidden field

// In HTML: <input type="text" name="website" style="display:none">
// Bots fill all fields, humans leave it empty
```

## Optional and stopOnFirstError

`optional()` and `stopOnFirstError()` are `Field` flags.

- `optional(true)`
  Skips validation when the value is `null` or an empty string (after sanitization).
- `stopOnFirstError(true)`
  Stops validation on the first failing rule for that field.

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Type;

$field = Type::string()
  ->optional()
  ->stopOnFirstError()
  ->addValidate(Level::STRICT, [Val::minLen(3)]);
```

## Nested paths and wildcards

Schema paths use dot notation:

```php
$schema = Schema::make()
  ->field('user.email', Type::email());
```

Wildcards let you target collections:

```php
$schema = Schema::make()
  ->field('items.*.name', Type::string());
```

When a wildcard matches multiple items, errors always contain a **concrete path**, e.g. `items.2.name`.

## Arrays: each, arrayOf, minItems/maxItems

### Validate an array itself

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make()
  ->field('tags', Type::array()->addValidate(Level::STRICT, [Val::minItems(2)]));
```

### Apply a Field to each element: `Schema::each()`

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make()
  ->each('tags', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));
```

### Array of elements: `Type::arrayOf()`

`Type::arrayOf()` returns an `ArrayOf` helper that can be applied to a `Schema`.

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make();

$schema = Type::arrayOf(Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]))
  ->minItems(2)
  ->applyTo($schema, 'tags');
```

This applies:

- validation to the array at `tags`
- validation/sanitization to each element at `tags.*`

## Objects: object and eachObject

### Nested object schema: `Schema::object()`

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$userSchema = Schema::make()
  ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
  ->field('name', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));

$schema = Schema::make()
  ->field('user', Type::object())
  ->object('user', $userSchema);
```

Child schema paths are relative. Errors and values are automatically prefixed, e.g. `user.name`.

**Field overrides**: If you define a field with a path that overlaps with an object schema, the field wins:

```php
$schema = Schema::make()
    ->object('user', $childSchema)
    ->field('user.email', Type::email()->addValidate(Level::STRICT, [Val::required()]));
// The field 'user.email' overrides the one from $childSchema
```

To prevent accidental overlaps, use `disallowOverlaps(true)`:

```php
$schema = Schema::make()
    ->disallowOverlaps(true)
    ->object('user', $childSchema)
    ->field('user.email', Type::email()); // Throws LogicException
```

### Collection of objects: `Schema::eachObject()`

```php
use InputGuard\Core\Level;
use InputGuard\Rules\Val\Val;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$itemSchema = Schema::make()
  ->field('name', Type::string()->addValidate(Level::STRICT, [Val::required()]))
  ->field('qty', Type::int()->addValidate(Level::STRICT, [Val::min(1)]));

$schema = Schema::make()
  ->eachObject('items', $itemSchema);
```

Errors are prefixed with the concrete index, e.g. `items.1.name`.

## Reject unknown fields

Enable strict mode to reject any input fields not explicitly declared in the schema:

```php
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

$schema = Schema::make()
    ->field('email', Type::email())
    ->field('name', Type::string())
    ->rejectUnknownFields();

$result = $schema->process([
    'email' => 'user@example.com',
    'name' => 'John',
    'extra' => 'malicious'  // Unknown field!
], Level::STRICT);

$result->ok(); // false
$result->errors()[0]->code; // 'unknown_field'
$result->errors()[0]->path; // 'extra'
```

This protects against **mass assignment** attacks and ensures only expected data is processed.

Works with nested objects and wildcards:

```php
$schema = Schema::make()
    ->field('items.*.name', Type::string())
    ->rejectUnknownFields();

// Input with 'items.0.extra' will produce error 'unknown_field' at path 'items.0.extra'
```

## Schema-level validators

For cross-field validation rules (like password confirmation), use schema-level validators:

```php
use InputGuard\Contract\SchemaValidator;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;
use InputGuard\Core\Level;
use InputGuard\Schema\Schema;
use InputGuard\Schema\Type;

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

$schema = Schema::make()
    ->field('password', Type::string())
    ->field('password_confirm', Type::string())
    ->rule(new PasswordConfirmValidator());

$result = $schema->process([
    'password' => 'secret',
    'password_confirm' => 'different'
], Level::STRICT);

$result->ok(); // false
```

Schema validators receive **sanitized values**, so you can rely on clean data.

## Policy versioning

Track which validation policy version was used to process input:

```php
$schema = Schema::make()
    ->policyVersion('1.2.3')
    ->field('email', Type::email());

$result = $schema->process(['email' => 'user@example.com'], Level::STRICT);

$result->meta()['policyVersion']; // '1.2.3'
```

Useful for auditing and debugging when validation rules change over time.

## Errors and translations

Validators should emit structured errors:

- `code` (stable)
- `meta` (technical details)
- `message` is usually `null` and resolved by a `Translator`

### Default catalog

```php
use InputGuard\Support\DefaultCatalog;
use InputGuard\Support\PresentableErrors;

$translator = DefaultCatalog::build();

$presentable = PresentableErrors::format(
  $result->errors(),
  $translator,
  'it'
);
```

### Custom catalog

```php
use InputGuard\Support\MessageCatalog;

$translator = new MessageCatalog([
  'en' => [
    'required' => fn($e) => "The field {$e->path} is required",
    'min_len' => fn($e) => "Minimum length is {$e->meta['min']}"
  ]
]);
```

## Extending

### Add a validator

- Implement `InputGuard\Contract\Validator`
- Return an array of `InputGuard\Core\Error`
- Emit `ErrorCode + meta` and keep `message` as `null`
- Expose it via `InputGuard\Rules\Val\Val` (factory methods)

### Add a sanitizer

- Implement `InputGuard\Contract\Sanitizer`
- Expose it via `InputGuard\Rules\San\San`

### Add a RuleSet

```php
use InputGuard\Core\Level;
use InputGuard\Schema\RuleSet;

$set = RuleSet::make()
  ->sanitize(Level::BASE, [/* ... */])
  ->validate(Level::STRICT, [/* ... */]);
```

### Add a Type

A Type is simply a method that returns a `Field` preset.

## Testing

```bash
./vendor/bin/phpunit
```