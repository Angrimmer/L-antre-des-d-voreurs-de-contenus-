<?php

namespace Antre\Tests;

use Antre\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $auth;

    protected function setUp(): void
    {
        $this->auth = new AuthService(TestDatabase::create());
    }

    // --- register ---

    public function testRegisterSuccess(): void
    {
        $result = $this->auth->register('alice', 'password123');
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);
    }

    public function testRegisterDuplicateUsernameReturnsError(): void
    {
        $this->auth->register('alice', 'password123');
        $result = $this->auth->register('alice', 'autremdp');
        $this->assertArrayHasKey('error', $result);
    }

    public function testRegisterUsernameTooShortReturnsError(): void
    {
        $result = $this->auth->register('ab', 'password123');
        $this->assertArrayHasKey('error', $result);
    }

    public function testRegisterUsernameTooLongReturnsError(): void
    {
        $result = $this->auth->register(str_repeat('a', 51), 'password123');
        $this->assertArrayHasKey('error', $result);
    }

    public function testRegisterPasswordTooShortReturnsError(): void
    {
        $result = $this->auth->register('alice', '12345');
        $this->assertArrayHasKey('error', $result);
    }

    public function testRegisterUsernameExactly3CharsIsValid(): void
    {
        $result = $this->auth->register('bob', 'password123');
        $this->assertTrue($result['success']);
    }

    public function testRegisterUsernameExactly50CharsIsValid(): void
    {
        $result = $this->auth->register(str_repeat('a', 50), 'password123');
        $this->assertTrue($result['success']);
    }

    public function testRegisterPasswordHashedInDb(): void
    {
        $this->auth->register('alice', 'monmotdepasse');
        // On vérifie que le mot de passe brut ne se retrouve pas en clair
        $result = $this->auth->login('alice', 'monmotdepasse');
        $this->assertTrue($result['success']);
    }

    // --- login ---

    public function testLoginSuccess(): void
    {
        $this->auth->register('alice', 'password123');
        $result = $this->auth->login('alice', 'password123');
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);
    }

    public function testLoginWrongPasswordReturnsError(): void
    {
        $this->auth->register('alice', 'password123');
        $result = $this->auth->login('alice', 'mauvaismdp');
        $this->assertArrayHasKey('error', $result);
    }

    public function testLoginUnknownUserReturnsError(): void
    {
        $result = $this->auth->login('fantome', 'password123');
        $this->assertArrayHasKey('error', $result);
    }

    public function testLoginIsCaseSensitiveForUsername(): void
    {
        $this->auth->register('alice', 'password123');
        $result = $this->auth->login('Alice', 'password123');
        $this->assertArrayHasKey('error', $result);
    }

    public function testLoginTrimsUsernameWhitespace(): void
    {
        $this->auth->register('alice', 'password123');
        $result = $this->auth->login('  alice  ', 'password123');
        $this->assertTrue($result['success']);
    }
}
