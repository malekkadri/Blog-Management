<?php

namespace App\Tests\Controller;

use App\Entity\PostTranslation;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\PostTranslationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FavoriteControllerTest extends WebTestCase
{
    public function testFavoritePostReturns404ForPostWithInvalidSlug(): void
    {
        self::expectException(NotFoundHttpException::class);

        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('POST', '/en/posts/invalid-slug/add-favorite');
        $client->request('POST', '/en/posts/invalid-slug/remove-favorite');
    }

    public function testGuestUserCannotAddPostToFavorites(): void
    {
        $client = static::createClient();

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/add-favorite');
        $client->request('POST', $path . '/remove-favorite');

        $client->followRedirect();

        self::assertRouteSame('app_login');
    }

    public function testAddPostToFavoritesWithInvalidCsrfToken(): void
    {
        $client = static::createClient();

        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/add-favorite', [
            '_token' => 'invalid_csrf_token',
        ], [], [
            'HTTP_REFERER' => '/en/posts/' . $postTranslation->getSlug(),
        ]);

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorExists('.add-to-favorites-form');
    }

    public function testAuthenticatedUserCanAddPostToFavorites(): void
    {
        $client = static::createClient();

        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getSlug();

        $client->request('GET', $path);

        $client->submitForm('Add to favorites');

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorExists('.remove-from-favorites-form');
    }

    public function testRemovePostFromFavoritesWithInvalidCsrfToken(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $client->loginUser($user = self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        // Add the post to favorites
        self::getContainer()->get(PostRepository::class)->addPostToFavorites($postTranslation->getPost()->getId(), $user->getId());

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/remove-favorite', [
            '_token' => 'invalid_csrf_token',
        ], [], [
            'HTTP_REFERER' => '/en/posts/' . $postTranslation->getSlug(),
        ]);

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorExists('.remove-from-favorites-form');
    }


    public function testAuthenticatedUserCanRemovePostFromFavorites(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $client->loginUser($user = self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        // Add the post to favorites
        self::getContainer()->get(PostRepository::class)->addPostToFavorites($postTranslation->getPost()->getId(), $user->getId());

        $path = '/en/posts/' . $postTranslation->getSlug();

        $client->request('GET', $path);

        $client->submitForm('Remove from favorites');

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorExists('.add-to-favorites-form');
    }
}
