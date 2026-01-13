<?php
namespace InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use InputGuard\Core\Error;
use InputGuard\Core\ErrorCode;
use InputGuard\Support\MessageCatalog;
use InputGuard\Support\PresentableErrors;

final class TranslatorTest extends TestCase {
    public function test_custom_message_override(): void {
        $catalog = new MessageCatalog([
            'it' => [
                ErrorCode::REQUIRED => fn(Error $e) => "Il campo {$e->path} è obbligatorio",
            ],
        ], 'it');

        $errors = [new Error('user.email', ErrorCode::REQUIRED, null, [])];

        $presented = PresentableErrors::format($errors, $catalog, 'it');

        $this->assertSame('Il campo user.email è obbligatorio', $presented[0]['message']);
    }
}