# InputGuard Examples

This directory contains practical examples demonstrating InputGuard features.

## Running Examples

```bash
cd examples
php 01-basic-validation.php
php 02-security-validation.php
php 03-nested-objects.php
php 04-cross-field-validation.php
php 05-translations.php
```

## Examples Overview

### 01-basic-validation.php
Basic field validation with types, levels, and common validators.

### 02-security-validation.php
Security-focused validation using paranoid presets to block:
- XSS attacks (HTML/script injection)
- Shell injection
- Path traversal
- Dangerous URLs
- Malicious file uploads

### 03-nested-objects.php
Working with nested object schemas, arrays of objects, and `rejectUnknownFields()`.

### 04-cross-field-validation.php
Schema-level validators for cross-field rules like:
- Password confirmation
- Date range validation

### 05-translations.php
Translating validation errors for display in multiple languages.
