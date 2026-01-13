<?php
namespace InputGuard\Core;

final class ErrorCode {
    // Generic
    public const REQUIRED = 'required';
    public const TYPE = 'type';
    public const INVALID = 'invalid';

    // String
    public const MIN_LEN = 'min_len';
    public const MAX_LEN = 'max_len';
    public const REGEX = 'regex';
    public const EMAIL = 'email';
    public const NO_CONTROL_CHARS = 'no_control_chars';
    public const INT = 'int';
    public const STRING = 'string';
    public const MIN = 'min';
    public const MAX = 'max';
    public const IN_SET = 'in_set';
    public const ARRAY = 'array';
    public const MIN_ITEMS = 'min_items';
    public const MAX_ITEMS = 'max_items';
    public const OBJECT = 'object';
    public const UNKNOWN_FIELD = 'unknown_field';
    public const NO_ZERO_WIDTH_CHARS = 'no_zero_width_chars';

    // Security validators
    public const NO_HTML_TAGS = 'no_html_tags';
    public const NO_SQL_PATTERNS = 'no_sql_patterns';
    public const NO_PATH_TRAVERSAL = 'no_path_traversal';
    public const NO_SHELL_CHARS = 'no_shell_chars';
    public const SAFE_URL = 'safe_url';
    public const SAFE_FILENAME = 'safe_filename';
    public const PRINTABLE_ONLY = 'printable_only';
    public const MAX_BYTES = 'max_bytes';

    // Anti-spam/bot validators
    public const GIBBERISH = 'gibberish';
    public const EXCESSIVE_URLS = 'excessive_urls';
    public const REPEATED_CHARS = 'repeated_chars';
    public const SPAM_KEYWORDS = 'spam_keywords';
    public const MIN_WORDS = 'min_words';
    public const MAX_WORDS = 'max_words';
    public const ALL_CAPS = 'all_caps';
    public const HONEYPOT = 'honeypot';
    public const SUSPICIOUS_PATTERN = 'suspicious_pattern';
}