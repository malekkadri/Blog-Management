<?php

namespace App\Tests\Controller;

use App\Entity\PostTranslation;
use App\Repository\PostTranslationRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostControllerTest extends WebTestCase
{
    public function testPostPageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        /** @var PostTranslation $postTranslation */
        $postTranslation = self::getContainer()->get(PostTranslationRepository::class)->findOneBy([
            'locale' => 'en',
        ]);

        $client->request('GET', '/en/posts/' . $postTranslation->getSlug());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $postTranslation->getTitle());
        self::assertSelectorTextContains('p.post-published', $postTranslation->getPost()->getPublishedAt()->format('d.m.Y'));
        self::assertSelectorTextContains('p.post-author', $postTranslation->getPost()->getAuthor()->getName());
        self::assertSelectorTextContains('p.post-content', $postTranslation->getContent());
        self::assertEquals(1, $client->getCrawler()->filter('img[src="' . $postTranslation->getPost()->getImageUrl() . '"]')->count());

        self::assertSelectorNotExists('.comment-form');
        // Guest users can't like or favorite posts
        self::assertSelectorNotExists('.post-actions');
    }

    public function testPostPageReturns404ForInvalidSlug(): void
    {
        self::expectException(NotFoundHttpException::class);

        $client = static::createClient();
        $client->catchExceptions(false);

        $client->request('GET', '/en/posts/invalid-slug');
    }
}
