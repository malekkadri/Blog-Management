<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Repository\TagRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostTestFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $faker;
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->faker = Factory::create();

        $this->tagRepository = $tagRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $this->tagRepository->findAll();

        for ($i = 0; $i < 20; $i++) {
            $data = $this->getPostData();

            $post = new Post();
            $post->setImage($data['image']);
            $post->setPublishedAt($data['publishedAt']);
            $post->setAuthor($this->getReference(UserFixtures::ADMIN_REFERENCE));

            $numberOfTags = random_int(1, 4);

            for ($j = 0; $j < $numberOfTags; $j++) {
                $tag = $tags[array_rand($tags)];
                $post->addTag($tag);
            }

            foreach ($data['translations'] as $locale => $value) {
                $postTranslation = new PostTranslation();
                $postTranslation->setLocale($locale);
                $postTranslation->setTitle($value['title']);
                $postTranslation->setContent($value['content']);

                $post->addTranslation($postTranslation);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagTestFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    private function getPostData(): array
    {
        return [
            'image' => 'post-images/' . md5(rand()) . '.png',
            'publishedAt' => DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 year', '-1 day')),
            'translations' => [
                'en' => [
                    'title' => $this->faker->unique()->sentence(),
                    'content' => $this->faker->realText(3000),
                ],
                'hr' => [
                    'title' => $this->faker->unique()->sentence(),
                    'content' => $this->faker->realText(3000),
                ],
            ],
        ];
    }
}
