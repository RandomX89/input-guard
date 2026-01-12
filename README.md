# InputGuard

InputGuard is a PHP library for **input sanitization and validation** with:

- **Deterministic pipelines** (sanitize then validate)
- **Severity levels** (`BASE`, `STRICT`, `PARANOID`, `PSYCHOTIC`)
- **Nested paths** with dot notation (`user.email`) and **wildcards** (`items.*.name`)
- **Composable presets** via `Type` and `RuleSet`
- **Localization-ready errors** (validators emit codes + meta, not user strings)

## Table of contents

- [Installation](#installation)
- [Core concepts](#core-concepts)
- [Levels](#levels)
- [Quick start](#quick-start)
- [Types](#types)
- [RuleSet presets](#ruleset-presets)
- [Optional and stopOnFirstError](#optional-and-stoponfirsterror)
- [Nested paths and wildcards](#nested-paths-and-wildcards)
- [Arrays: each, arrayOf, minItems/maxItems](#arrays-each-arrayof-minitemsmaxitems)
- [Objects: object and eachObject](#objects-object-and-eachobject)
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
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

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
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Type;

$field = Type::string()
  ->addValidate(Level::STRICT, [Val::minLen(3)]);
```

## RuleSet presets

RuleSets are composable rule bundles.

Built-in presets:

- `RuleSet::username(int $min = 3, int $max = 30)`
- `RuleSet::slug(int $max = 80)`
- `RuleSet::email()`

Apply a RuleSet to a Field:

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Type;

$field = Type::string()
  ->use(RuleSet::username())
  ->addValidate(Level::STRICT, [Val::required()]);
```

Merge RuleSets (order matters: A then B):

```php
use RandomX98\InputGuard\Schema\RuleSet;

$rules = RuleSet::username()->merge(RuleSet::slug());
```

## Optional and stopOnFirstError

`optional()` and `stopOnFirstError()` are `Field` flags.

- `optional(true)`
  Skips validation when the value is `null` or an empty string (after sanitization).
- `stopOnFirstError(true)`
  Stops validation on the first failing rule for that field.

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Type;

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
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

$schema = Schema::make()
  ->field('tags', Type::array()->addValidate(Level::STRICT, [Val::minItems(2)]));
```

### Apply a Field to each element: `Schema::each()`

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

$schema = Schema::make()
  ->each('tags', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));
```

### Array of elements: `Type::arrayOf()`

`Type::arrayOf()` returns an `ArrayOf` helper that can be applied to a `Schema`.

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

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
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

$userSchema = Schema::make()
  ->field('email', Type::email()->addValidate(Level::STRICT, [Val::required()]))
  ->field('name', Type::string()->addValidate(Level::STRICT, [Val::minLen(2)]));

$schema = Schema::make()
  ->field('user', Type::object())
  ->object('user', $userSchema);
```

Child schema paths are relative. Errors and values are automatically prefixed, e.g. `user.name`.

### Collection of objects: `Schema::eachObject()`

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\Schema;
use RandomX98\InputGuard\Schema\Type;

$itemSchema = Schema::make()
  ->field('name', Type::string()->addValidate(Level::STRICT, [Val::required()]))
  ->field('qty', Type::int()->addValidate(Level::STRICT, [Val::min(1)]));

$schema = Schema::make()
  ->eachObject('items', $itemSchema);
```

Errors are prefixed with the concrete index, e.g. `items.1.name`.

## Errors and translations

Validators should emit structured errors:

- `code` (stable)
- `meta` (technical details)
- `message` is usually `null` and resolved by a `Translator`

### Default catalog

```php
use RandomX98\InputGuard\Support\DefaultCatalog;
use RandomX98\InputGuard\Support\PresentableErrors;

$translator = DefaultCatalog::build();

$presentable = PresentableErrors::format(
  $result->errors(),
  $translator,
  'it'
);
```

### Custom catalog

```php
use RandomX98\InputGuard\Support\MessageCatalog;

$translator = new MessageCatalog([
  'en' => [
    'required' => fn($e) => "The field {$e->path} is required",
    'min_len' => fn($e) => "Minimum length is {$e->meta['min']}"
  ]
]);
```

## Extending

### Add a validator

- Implement `RandomX98\InputGuard\Contract\Validator`
- Return an array of `RandomX98\InputGuard\Core\Error`
- Emit `ErrorCode + meta` and keep `message` as `null`
- Expose it via `RandomX98\InputGuard\Rules\Val\Val` (factory methods)

### Add a sanitizer

- Implement `RandomX98\InputGuard\Contract\Sanitizer`
- Expose it via `RandomX98\InputGuard\Rules\San\San`

### Add a RuleSet

```php
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Schema\RuleSet;

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