<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testGuestUserCanSeeLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Login');
    }

    public function testAuthenticatedUsersAreRedirectedFromLoginToHomepage(): void
    {
        $client = static::createClient();
        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));
        $client->request('GET', '/en/login');

        self::assertResponseRedirects('/en/');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/login');

        $client->submitForm('Login', [
            '_username' => 'admin@test.com',
            '_password' => 'admin12345',
        ]);

        $client->followRedirect();
        self::assertRouteSame('app_home');
        self::assertNull($client->getCookieJar()->get('REMEMBERME'));
    }

    public function testLoginWithRememberMe(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/login');

        $client->submitForm('Login', [
            '_username' => 'admin@test.com',
            '_password' => 'admin12345',
            '_remember_me' => 'on',
        ]);

        $client->followRedirect();
        self::assertRouteSame('app_home');
        self::assertNotNull($client->getCookieJar()->get('REMEMBERME'));
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/login');

        $client->submitForm('Login', [
            '_username' => 'wrong',
            '_password' => 'password',
        ]);

        $client->followRedirect();
        self::assertRouteSame('app_login');
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    public function testLogoutRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/en/logout');

        $client->followRedirect();

        self::assertRouteSame('app_login');
    }

    public function testSuccessfulLogout(): void
    {
        $client = static::createClient();
        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));
        $client->request('POST', '/en/logout');

        $client->followRedirect();

        self::assertRouteSame('app_home');
    }
}
