<?php

namespace Antre\Tests;

use Antre\ContactValidator;
use PHPUnit\Framework\TestCase;

class ContactValidatorTest extends TestCase
{
    private function valid(): array
    {
        return ['name' => 'Jean Dupont', 'email' => 'jean@example.com', 'message' => 'Bonjour !'];
    }

    // --- sanitize ---

    public function testSanitizeStripsHtmlTags(): void
    {
        $result = ContactValidator::sanitize([
            'name'    => '<b>Jean</b>',
            'email'   => 'jean@example.com',
            'message' => '<b>Bonjour</b>',
        ]);
        // strip_tags supprime les balises mais conserve le texte entre elles
        $this->assertSame('Jean', $result['name']);
        $this->assertSame('Bonjour', $result['message']);
        // Aucune balise HTML ne subsiste
        $this->assertStringNotContainsString('<', $result['name']);
        $this->assertStringNotContainsString('<', $result['message']);
    }

    public function testSanitizeTrimsWhitespace(): void
    {
        $result = ContactValidator::sanitize(['name' => '  Alice  ', 'email' => ' a@b.com ', 'message' => ' ok ']);
        $this->assertSame('Alice', $result['name']);
        $this->assertSame('a@b.com', $result['email']);
    }

    public function testSanitizeHandlesMissingKeys(): void
    {
        $result = ContactValidator::sanitize([]);
        $this->assertSame('', $result['name']);
        $this->assertSame('', $result['email']);
        $this->assertSame('', $result['message']);
    }

    // --- champs obligatoires ---

    public function testValidReturnsNull(): void
    {
        $this->assertNull(ContactValidator::validate($this->valid()));
    }

    public function testEmptyNameReturnsError(): void
    {
        $this->assertNotNull(ContactValidator::validate(array_merge($this->valid(), ['name' => ''])));
    }

    public function testEmptyEmailReturnsError(): void
    {
        $this->assertNotNull(ContactValidator::validate(array_merge($this->valid(), ['email' => ''])));
    }

    public function testEmptyMessageReturnsError(): void
    {
        $this->assertNotNull(ContactValidator::validate(array_merge($this->valid(), ['message' => ''])));
    }

    // --- longueurs ---

    public function testNameTooLongReturnsError(): void
    {
        $data = array_merge($this->valid(), ['name' => str_repeat('a', 81)]);
        $this->assertStringContainsString('80', ContactValidator::validate($data));
    }

    public function testNameExactly80CharsIsValid(): void
    {
        $data = array_merge($this->valid(), ['name' => str_repeat('a', 80)]);
        $this->assertNull(ContactValidator::validate($data));
    }

    public function testMessageTooLongReturnsError(): void
    {
        $data = array_merge($this->valid(), ['message' => str_repeat('a', 2001)]);
        $this->assertStringContainsString('2000', ContactValidator::validate($data));
    }

    public function testMessageExactly2000CharsIsValid(): void
    {
        $data = array_merge($this->valid(), ['message' => str_repeat('a', 2000)]);
        $this->assertNull(ContactValidator::validate($data));
    }

    // --- validation email ---

    public function testInvalidEmailReturnsError(): void
    {
        foreach (['notanemail', '@no-local.com', 'no-at-sign', 'a@', '@b.com'] as $bad) {
            $data = array_merge($this->valid(), ['email' => $bad]);
            $this->assertNotNull(ContactValidator::validate($data), "Expected error for: $bad");
        }
    }

    public function testValidEmailFormats(): void
    {
        foreach (['a@b.com', 'user+tag@domain.fr', 'x.y@z.co.uk'] as $ok) {
            $data = array_merge($this->valid(), ['email' => $ok]);
            $this->assertNull(ContactValidator::validate($data), "Expected valid for: $ok");
        }
    }

    // --- injection d'en-têtes mail ---

    public function testCrlfInNameReturnsError(): void
    {
        $data = array_merge($this->valid(), ['name' => "Jean\r\nBcc: hacker@evil.com"]);
        $this->assertNotNull(ContactValidator::validate($data));
    }

    public function testCrlfInEmailReturnsError(): void
    {
        $data = array_merge($this->valid(), ['email' => "a@b.com\r\nBcc: hacker@evil.com"]);
        $this->assertNotNull(ContactValidator::validate($data));
    }

    // --- regex nom ---

    public function testNameWithNumbersReturnsError(): void
    {
        $data = array_merge($this->valid(), ['name' => 'Jean123']);
        $this->assertNotNull(ContactValidator::validate($data));
    }

    public function testNameWithAccentsIsValid(): void
    {
        $data = array_merge($this->valid(), ['name' => 'Éléonore Müller']);
        $this->assertNull(ContactValidator::validate($data));
    }

    public function testNameWithHyphenAndApostropheIsValid(): void
    {
        $data = array_merge($this->valid(), ['name' => "Jean-Pierre O'Brien"]);
        $this->assertNull(ContactValidator::validate($data));
    }
}
