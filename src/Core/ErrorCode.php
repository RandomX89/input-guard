<?php
namespace RandomX98\InputGuard\Core;

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
}