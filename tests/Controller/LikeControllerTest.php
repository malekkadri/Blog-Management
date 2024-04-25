<?php

namespace App\Tests\Controller;

use App\Entity\PostTranslation;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\PostTranslationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LikeControllerTest extends WebTestCase
{
    public function testLikeReturns404ForPostWithInvalidSlug(): void
    {
        self::expectException(NotFoundHttpException::class);

        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('POST', '/en/posts/invalid-slug/like');
        $client->request('POST', '/en/posts/invalid-slug/unlike');
    }

    public function testGuestUserCannotLikePost(): void
    {
        $client = static::createClient();

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/like');
        $client->request('POST', $path . '/unlike');

        $client->followRedirect();

        self::assertRouteSame('app_login');
    }

    public function testLikePostWithInvalidCsrfToken(): void
    {
        $client = static::createClient();

        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/like', [
            '_token' => 'invalid_csrf_token',
        ], [], [
            'HTTP_REFERER' => '/en/posts/' . $postTranslation->getSlug(),
        ]);

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorTextContains('.likes-count', 'Likes: 0');
    }

    public function testAuthenticatedUserCanLikePost(): void
    {
        $client = static::createClient();

        $client->loginUser(self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $path = '/en/posts/' . $postTranslation->getSlug();

        $client->request('GET', $path);

        $client->submitForm('Like post');

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorTextContains('.likes-count', 'Likes: 1');
    }

    public function testUnlikePostWithInvalidCsrfToken(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $client->loginUser($user = self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        // Like the post
        self::getContainer()->get(PostRepository::class)->likePost($postTranslation->getPost()->getId(), $user->getId());

        $path = '/en/posts/' . $postTranslation->getPost()->getId();

        $client->request('POST', $path . '/like', [
            '_token' => 'invalid_csrf_token',
        ], [], [
            'HTTP_REFERER' => '/en/posts/' . $postTranslation->getSlug(),
        ]);

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorTextContains('.likes-count', 'Likes: 1');
    }


    public function testAuthenticatedUserCanUnlikePost(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $client->loginUser($user = self::getContainer()->get(UserRepository::class)->find(1));

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        // Like the post
        self::getContainer()->get(PostRepository::class)->likePost($postTranslation->getPost()->getId(), $user->getId());

        $path = '/en/posts/' . $postTranslation->getSlug();

        $client->request('GET', $path);

        $client->submitForm('Unlike post');

        $client->followRedirect();

        self::assertRouteSame('app_post', ['slug' => $postTranslation->getSlug()]);
        self::assertSelectorTextContains('.likes-count', 'Likes: 0');
    }
}
