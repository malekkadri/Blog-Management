<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomepage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Homepage');

        self::assertEquals(9, $crawler->filter('.card')->count());
        self::assertSelectorExists('.pagination');
    }

    public function testLoginAndRegisterLinksForGuestUsers(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();

        self::assertEquals(1, $crawler->filter('a:contains("Login")')->count());
        self::assertEquals(1, $crawler->filter('a:contains("Register")')->count());

        self::assertEquals(0, $crawler->filter('a:contains("Profile")')->count());
        self::assertEquals(0, $crawler->filter('button:contains("Logout")')->count());
    }

    public function testNavigationLinksForAuthenticatedUsers(): void
    {
        $client = static::createClient();
        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));
        $crawler = $client->request('GET', '/en/');

        $this->assertResponseIsSuccessful();

        self::assertEquals(0, $crawler->filter('a:contains("Login")')->count());
        self::assertEquals(0, $crawler->filter('a:contains("Register")')->count());

        self::assertEquals(1, $crawler->filter('a:contains("Profile")')->count());
        self::assertEquals(1, $crawler->filter('button:contains("Logout")')->count());
    }

    public function testPostsPagination(): void
    {
        $client = static::createClient();
        // We have total of 20 posts in the database, 9 of them are displayed per page
        $client->request('GET', '/en/?page=3');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Homepage');

        self::assertEquals(2, $client->getCrawler()->filter('.card')->count());
        self::assertSelectorExists('.pagination');
    }

    public function testSearch(): void
    {
        $client = static::createClient();

        $this->createTestPostForSearch();

        $client->request('GET', '/en/?q=testing');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Homepage');

        self::assertEquals(1, $client->getCrawler()->filter('.card')->count());
        self::assertSelectorNotExists('.pagination');
    }

    public function testSearchWithNoResults(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/?q=testing');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Homepage');

        self::assertEquals(0, $client->getCrawler()->filter('.card')->count());
        self::assertSelectorNotExists('.pagination');
    }

    private function createTestPostForSearch(): void
    {
        /** @var PostRepository $postRepository */
        $postRepository = self::getContainer()->get(PostRepository::class);
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);

        $post = new Post();
        $post->setImage('image.jpg');
        $post->setPublishedAt(new DateTimeImmutable());
        $post->setAuthor($userRepository->find(1));

        $postTranslation = new PostTranslation();
        $postTranslation->setLocale('en');
        $postTranslation->setTitle('Testing');
        $postTranslation->setContent('Testing content');

        $post->addTranslation($postTranslation);

        $postRepository->save($post, true);
    }
}
