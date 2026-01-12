<?php
namespace RandomX98\InputGuard\Rules\Val;

use RandomX98\InputGuard\Contract\Validator;
use RandomX98\InputGuard\Core\Error;
use RandomX98\InputGuard\Core\ErrorCode;

final class SafeFilenameValidator implements Validator {
    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar',
        'exe', 'bat', 'cmd', 'com', 'msi',
        'sh', 'bash', 'zsh', 'csh',
        'js', 'vbs', 'wsf', 'wsh',
        'ps1', 'psm1',
        'jar', 'class',
        'py', 'pyc', 'pyo',
        'pl', 'pm', 'cgi',
        'rb', 'erb',
        'asp', 'aspx', 'asa', 'asax', 'ascx', 'ashx', 'asmx',
        'jsp', 'jspx',
        'htaccess', 'htpasswd',
        'svg',
    ];

    public function __construct(
        private readonly bool $blockDangerousExtensions = true
    ) {}

    public function validate(mixed $value, array $context = []): array {
        if (!is_string($value) || $value === '') {
            return [];
        }

        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/', $value) !== 1) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::SAFE_FILENAME,
                null,
                ['reason' => 'invalid_chars']
            )];
        }

        if (str_starts_with($value, '.') || str_starts_with($value, '-')) {
            return [new Error(
                $context['path'] ?? '',
                ErrorCode::SAFE_FILENAME,
                null,
                ['reason' => 'invalid_start']
            )];
        }

        if ($this->blockDangerousExtensions) {
            $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            if (in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
                return [new Error(
                    $context['path'] ?? '',
                    ErrorCode::SAFE_FILENAME,
                    null,
                    ['reason' => 'dangerous_extension', 'extension' => $ext]
                )];
            }
        }

        return [];
    }
}
