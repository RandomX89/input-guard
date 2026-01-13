# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-13

### Added

- **Core**
  - `Schema` class for defining input validation schemas
  - `Field` class with sanitize→validate pipeline
  - `Level` enum: `BASE`, `STRICT`, `PARANOID`, `PSYCHOTIC`
  - `Result` class with `ok()`, `values()`, `errors()`, `meta()`
  - `Error` class with structured error information

- **Types**
  - `Type::string()`, `Type::email()`, `Type::int()`, `Type::array()`, `Type::object()`
  - `Type::arrayOf()` for typed array validation

- **Sanitizers**
  - `San::trim()` - Trim whitespace
  - `San::nullIfEmpty()` - Convert empty to null
  - `San::lowercase()` - Convert to lowercase
  - `San::toInt()` - Cast to integer
  - `San::normalizeNfkc()` - NFKC Unicode normalization
  - `San::stripTags()` - Remove HTML tags
  - `San::htmlEntities()` - Encode HTML entities

- **Validators - Basic**
  - `Val::required()`, `Val::typeString()`, `Val::typeInt()`, `Val::typeArray()`
  - `Val::minLen()`, `Val::maxLen()`, `Val::min()`, `Val::max()`
  - `Val::email()`, `Val::regex()`, `Val::inSet()`
  - `Val::minItems()`, `Val::maxItems()`

- **Validators - Security**
  - `Val::noControlChars()` - Block control characters
  - `Val::noZeroWidthChars()` - Block zero-width Unicode
  - `Val::noHtmlTags()` - Block HTML tags
  - `Val::noSqlPatterns()` - Block SQL injection patterns
  - `Val::noPathTraversal()` - Block path traversal attempts
  - `Val::noShellChars()` - Block shell metacharacters
  - `Val::safeUrl()` - Block dangerous URL schemes
  - `Val::safeFilename()` - Validate safe filenames
  - `Val::printableOnly()` - Only printable ASCII
  - `Val::maxBytes()` - Maximum byte length

- **Validators - Anti-Spam**
  - `Val::noGibberish()` - Detect keyboard mashing
  - `Val::noExcessiveUrls()` - Limit URL count
  - `Val::noRepeatedChars()` - Block character spam
  - `Val::noSpamKeywords()` - Block spam phrases
  - `Val::minWords()`, `Val::maxWords()` - Word count limits
  - `Val::noAllCaps()` - Block ALL CAPS text
  - `Val::honeypot()` - Hidden field trap for bots
  - `Val::noSuspiciousPattern()` - Detect suspicious patterns

- **RuleSet Presets**
  - `RuleSet::username()`, `RuleSet::slug()`, `RuleSet::email()`
  - `RuleSet::paranoidString()` - Maximum security for untrusted input
  - `RuleSet::paranoidUrl()` - Secure URL validation
  - `RuleSet::paranoidFilename()` - Secure filename validation
  - `RuleSet::antiSpam()` - Anti-bot heuristics
  - `RuleSet::antiSpamStrict()` - Anti-spam + security combined
  - `RuleSet::honeypot()` - Bot trap field

- **Schema Features**
  - Nested paths with dot notation (`user.email`)
  - Wildcards for collections (`items.*.name`)
  - `rejectUnknownFields()` - Block mass assignment
  - `Schema::object()` and `Schema::eachObject()` for nested schemas
  - Schema-level validators via `Schema::rule()`
  - Policy versioning via `Schema::policyVersion()`

- **Translations**
  - `DefaultCatalog` with English and Italian translations
  - `MessageCatalog` for custom translations
  - `PresentableErrors` for formatting errors

### Security

- Protection against XSS, SQL injection, path traversal, shell injection
- Unicode attack prevention (zero-width chars, control chars)
- Anti-bot/spam heuristics for public forms
