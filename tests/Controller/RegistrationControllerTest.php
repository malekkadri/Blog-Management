<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testGuestUserCanSeeRegisterPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Register');
    }

    public function testAuthenticatedUsersAreRedirectedFromRegisterToHomepage(): void
    {
        $client = static::createClient();
        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));
        $client->request('GET', '/en/register');

        self::assertResponseRedirects('/en/');
    }

    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/register');

        $client->submitForm('Register', [
            'registration_form[name]' => 'John Doe',
            'registration_form[email]' => 'john@test.com',
            'registration_form[plainPassword]' => 'john12345',
            'registration_form[agreeTerms]' => true,
        ]);

        /** @var User $user */
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'john@test.com']);

        $client->followRedirect();
        self::assertRouteSame('app_home');

        self::assertNotNull($user);
        self::assertSame('John Doe', $user->getName());
        self::assertSame('john@test.com', $user->getEmail());
        self::assertSame([User::ROLE_USER], $user->getRoles());
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testRegisterWithInvalidData(string $field, $invalidData, string $error): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/register');

        $validData = [
            'registration_form[name]' => 'John Doe',
            'registration_form[email]' => 'john@test.com',
            'registration_form[plainPassword]' => 'john12345',
            'registration_form[agreeTerms]' => true,
        ];

        $client->submitForm('Register', array_merge($validData, [$field => $invalidData]));

        self::assertRouteSame('app_register');
        self::assertSelectorTextContains('.invalid-feedback', $error);
    }

    public function invalidDataProvider(): Generator
    {
        yield ['registration_form[name]', '', 'This value should not be blank.'];
        yield ['registration_form[name]', 'a', 'This value is too short. It should have 2 characters or more.'];
        yield ['registration_form[name]', str_repeat('a', 256), 'This value is too long. It should have 255 characters or less.'];
        yield ['registration_form[email]', '', 'This value should not be blank.'];
        yield ['registration_form[email]', 'invalid-email', 'This value is not a valid email address.'];
        yield ['registration_form[email]', str_repeat('a', 181) . '@test.com', 'This value is too long. It should have 180 characters or less.'];
        yield ['registration_form[email]', 'admin@test.com', 'There is already an account with this email'];
        yield ['registration_form[plainPassword]', '', 'Please enter a password'];
        yield ['registration_form[plainPassword]', '12345', 'Your password should be at least 6 characters'];
        yield ['registration_form[plainPassword]', str_repeat('a', 4097), 'This value is too long. It should have 4096 characters or less.'];
    }
}
