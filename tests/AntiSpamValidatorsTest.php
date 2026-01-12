<?php
namespace RandomX98\InputGuard\Tests;

use PHPUnit\Framework\TestCase;
use RandomX98\InputGuard\Core\Level;
use RandomX98\InputGuard\Rules\Val\Val;
use RandomX98\InputGuard\Schema\RuleSet;
use RandomX98\InputGuard\Schema\Schema;

final class AntiSpamValidatorsTest extends TestCase {
    public function test_no_gibberish_detects_keyboard_mashing(): void {
        $v = Val::noGibberish();
        $errs = $v->validate('asdfghjkl qwerty', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('gibberish', $errs[0]->code);
    }

    public function test_no_gibberish_detects_excessive_consonants(): void {
        $v = Val::noGibberish();
        $errs = $v->validate('bcdfghjklmnpqrstvwxyz', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('gibberish', $errs[0]->code);
    }

    public function test_no_gibberish_passes_normal_text(): void {
        $v = Val::noGibberish();
        $errs = $v->validate('Hello, this is a normal message.', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_excessive_urls_blocks_spam(): void {
        $v = Val::noExcessiveUrls(2);
        $text = 'Check out https://spam1.com and https://spam2.com and https://spam3.com';
        $errs = $v->validate($text, ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('excessive_urls', $errs[0]->code);
    }

    public function test_no_excessive_urls_allows_few_links(): void {
        $v = Val::noExcessiveUrls(2);
        $text = 'Check out https://example.com for more info.';
        $errs = $v->validate($text, ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_repeated_chars_detects_spam(): void {
        $v = Val::noRepeatedChars(4);
        $errs = $v->validate('Hellooooooo!!!!!!', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('repeated_chars', $errs[0]->code);
    }

    public function test_no_repeated_chars_passes_normal(): void {
        $v = Val::noRepeatedChars(4);
        $errs = $v->validate('Hello there!', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_no_spam_keywords_detects_common_spam(): void {
        $v = Val::noSpamKeywords();
        $errs = $v->validate('Make money fast with our amazing offer!', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('spam_keywords', $errs[0]->code);
    }

    public function test_no_spam_keywords_passes_normal(): void {
        $v = Val::noSpamKeywords();
        $errs = $v->validate('I would like to report an issue with my order.', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_min_words_requires_minimum(): void {
        $v = Val::minWords(5);
        $errs = $v->validate('Too short', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('min_words', $errs[0]->code);
    }

    public function test_max_words_limits_length(): void {
        $v = Val::maxWords(5);
        $errs = $v->validate('This is a very long message that exceeds the limit', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('max_words', $errs[0]->code);
    }

    public function test_no_all_caps_blocks_shouting(): void {
        $v = Val::noAllCaps();
        $errs = $v->validate('THIS IS ALL CAPS AND VERY ANNOYING', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('all_caps', $errs[0]->code);
    }

    public function test_no_all_caps_allows_normal(): void {
        $v = Val::noAllCaps();
        $errs = $v->validate('This is Normal Text with Some Caps', ['path' => 'field']);
        $this->assertCount(0, $errs);
    }

    public function test_honeypot_blocks_filled_field(): void {
        $v = Val::honeypot();
        $errs = $v->validate('bot filled this', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('honeypot', $errs[0]->code);
    }

    public function test_honeypot_passes_empty_field(): void {
        $v = Val::honeypot();
        $this->assertCount(0, $v->validate('', ['path' => 'field']));
        $this->assertCount(0, $v->validate(null, ['path' => 'field']));
    }

    public function test_no_suspicious_pattern_detects_excessive_punctuation(): void {
        $v = Val::noSuspiciousPattern();
        $errs = $v->validate('Hello!!!???!!!???...!!!', ['path' => 'field']);
        $this->assertCount(1, $errs);
        $this->assertSame('suspicious_pattern', $errs[0]->code);
    }

    public function test_anti_spam_preset_blocks_gibberish(): void {
        $schema = Schema::make()
            ->field('message', RuleSet::antiSpam()->toField());

        $r = $schema->process(['message' => 'asdfghjkl qwerty zxcvbnm'], Level::PARANOID);
        $this->assertFalse($r->ok());
        $this->assertSame('gibberish', $r->errors()[0]->code);
    }

    public function test_anti_spam_preset_blocks_short_messages(): void {
        $schema = Schema::make()
            ->field('message', RuleSet::antiSpam()->toField());

        $r = $schema->process(['message' => 'Hi'], Level::STRICT);
        $this->assertFalse($r->ok());
        $this->assertSame('min_words', $r->errors()[0]->code);
    }

    public function test_anti_spam_preset_passes_valid_message(): void {
        $schema = Schema::make()
            ->field('message', RuleSet::antiSpam()->toField());

        $r = $schema->process([
            'message' => 'Hello, I would like to report an issue with my recent order. The product arrived damaged and I need a replacement.'
        ], Level::PARANOID);

        $this->assertTrue($r->ok());
    }

    public function test_honeypot_preset_catches_bots(): void {
        $schema = Schema::make()
            ->field('email', RuleSet::email()->toField())
            ->field('website', RuleSet::honeypot()->toField());

        $r = $schema->process([
            'email' => 'user@example.com',
            'website' => 'http://spam.com',
        ], Level::BASE);

        $this->assertFalse($r->ok());
        $this->assertSame('honeypot', $r->errors()[0]->code);
    }
}
